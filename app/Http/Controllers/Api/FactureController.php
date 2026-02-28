<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FactureStoreRequest;
use App\Http\Requests\Api\FactureUpdateRequest;
use App\Http\Resources\FactureResource;
use App\Models\Facture;
use App\Services\FactureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FactureController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FactureService $factureService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));

        $query = Facture::query()
            ->with(['client:id,nom', 'commercial:id,nom', 'pointVente:id,nom'])
            ->withSum('paiements as montant_paye', 'montant')
            ->orderByDesc('id');

        if ($request->filled('statut')) {
            $query->where('statut', (string) $request->query('statut'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', (int) $request->query('client_id'));
        }

        if ($request->filled('du')) {
            $query->whereDate('date_emission', '>=', (string) $request->query('du'));
        }

        if ($request->filled('au')) {
            $query->whereDate('date_emission', '<=', (string) $request->query('au'));
        }

        if ($request->filled('recherche')) {
            $recherche = trim((string) $request->query('recherche'));
            $query->where('numero_facture', 'like', "%{$recherche}%");
        }

        $paginator = $query->paginate($parPage)->withQueryString();
        $elements = FactureResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des factures recuperee avec succes.', $paginator, $elements);
    }

    public function store(FactureStoreRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $facture = DB::transaction(function () use ($payload): Facture {
            $facture = Facture::query()->create([
                'numero_facture' => trim((string) $payload['numero_facture']),
                'date_emission' => $payload['date_emission'],
                'date_echeance' => $payload['date_echeance'] ?? null,
                'client_id' => $payload['client_id'],
                'commercial_id' => $payload['commercial_id'] ?? null,
                'point_vente_id' => $payload['point_vente_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'statut' => 'BROUILLON',
            ]);

            foreach ($payload['lignes'] as $ligne) {
                $quantite = (int) $ligne['quantite'];
                $prixUnitaire = (float) $ligne['prix_unitaire'];

                $facture->factureLignes()->create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'description' => $ligne['description'] ?? null,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'total_ligne' => $quantite * $prixUnitaire,
                ]);
            }

            $this->factureService->decrementerStockDepuisLignes($facture);
            $this->factureService->recalculerTotaux($facture);
            $this->factureService->recalculerStatut($facture);

            return $facture;
        });

        $facture->load(['client:id,nom', 'commercial:id,nom', 'pointVente:id,nom', 'factureLignes.produit:id,nom'])
            ->loadSum('paiements as montant_paye', 'montant');

        return $this->succes('Facture creee avec succes.', (new FactureResource($facture))->resolve(), 201);
    }

    public function show(Facture $facture): JsonResponse
    {
        $facture->load(['client:id,nom', 'commercial:id,nom', 'pointVente:id,nom', 'factureLignes.produit:id,nom'])
            ->loadSum('paiements as montant_paye', 'montant');

        return $this->succes('Facture recuperee avec succes.', (new FactureResource($facture))->resolve());
    }

    public function update(FactureUpdateRequest $request, Facture $facture): JsonResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return $this->echec(
                'Impossible de modifier une facture annulee.',
                ['statut' => ['La facture est deja annulee.']],
                409
            );
        }

        $payload = $request->validated();

        DB::transaction(function () use ($payload, $facture): void {
            $this->factureService->incrementerStockDepuisLignes($facture);

            $facture->update([
                'numero_facture' => trim((string) $payload['numero_facture']),
                'date_emission' => $payload['date_emission'],
                'date_echeance' => $payload['date_echeance'] ?? null,
                'client_id' => $payload['client_id'],
                'commercial_id' => $payload['commercial_id'] ?? null,
                'point_vente_id' => $payload['point_vente_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            $facture->factureLignes()->delete();

            foreach ($payload['lignes'] as $ligne) {
                $quantite = (int) $ligne['quantite'];
                $prixUnitaire = (float) $ligne['prix_unitaire'];

                $facture->factureLignes()->create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'description' => $ligne['description'] ?? null,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'total_ligne' => $quantite * $prixUnitaire,
                ]);
            }

            $this->factureService->decrementerStockDepuisLignes($facture);
            $this->factureService->recalculerTotaux($facture);
            $this->factureService->recalculerStatut($facture);
        });

        $facture->refresh();
        $facture->load(['client:id,nom', 'commercial:id,nom', 'pointVente:id,nom', 'factureLignes.produit:id,nom'])
            ->loadSum('paiements as montant_paye', 'montant');

        return $this->succes('Facture mise a jour avec succes.', (new FactureResource($facture))->resolve());
    }

    public function annuler(Facture $facture): JsonResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return $this->echec(
                'La facture est deja annulee.',
                ['statut' => ['La facture est deja annulee.']],
                409
            );
        }

        DB::transaction(function () use ($facture): void {
            $this->factureService->incrementerStockDepuisLignes($facture);
            $facture->statut = 'ANNULEE';
            $facture->save();
        });

        $facture->load(['client:id,nom', 'commercial:id,nom', 'pointVente:id,nom', 'factureLignes.produit:id,nom'])
            ->loadSum('paiements as montant_paye', 'montant');

        return $this->succes('Facture annulee avec succes.', (new FactureResource($facture))->resolve());
    }

    public function destroy(Facture $facture): JsonResponse
    {
        if ($facture->statut !== 'BROUILLON') {
            return $this->echec(
                'Suppression interdite. Seules les factures BROUILLON peuvent etre supprimees.',
                ['statut' => ['La facture doit etre en statut BROUILLON pour etre supprimee.']],
                409
            );
        }

        try {
            DB::transaction(function () use ($facture): void {
                $this->factureService->incrementerStockDepuisLignes($facture);
                $facture->delete();
            });
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer cette facture.',
                ['suppression' => ['Une erreur est survenue pendant la suppression.']],
                409
            );
        }

        return $this->succes('Facture supprimee avec succes.');
    }
}

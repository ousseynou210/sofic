<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaiementStoreRequest;
use App\Http\Requests\Api\PaiementUpdateRequest;
use App\Http\Resources\PaiementResource;
use App\Models\Facture;
use App\Models\Paiement;
use App\Services\FactureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaiementController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FactureService $factureService)
    {
    }

    public function liste(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));

        $query = Paiement::query()
            ->with(['compte:id,nom,type', 'facture:id,numero_facture,statut,total_facture'])
            ->orderByDesc('date_paiement')
            ->orderByDesc('id');

        if ($request->filled('facture_id')) {
            $query->where('facture_id', (int) $request->query('facture_id'));
        }

        if ($request->filled('compte_id')) {
            $query->where('compte_id', (int) $request->query('compte_id'));
        }

        if ($request->filled('du')) {
            $query->whereDate('date_paiement', '>=', (string) $request->query('du'));
        }

        if ($request->filled('au')) {
            $query->whereDate('date_paiement', '<=', (string) $request->query('au'));
        }

        if ($request->filled('mode')) {
            $query->where('mode', (string) $request->query('mode'));
        }

        if ($request->filled('recherche')) {
            $recherche = trim((string) $request->query('recherche'));
            $query->where('reference', 'like', "%{$recherche}%");
        }

        $paginator = $query->paginate($parPage)->withQueryString();
        $elements = PaiementResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des paiements recuperee avec succes.', $paginator, $elements);
    }

    public function index(Facture $facture): JsonResponse
    {
        $paiements = $facture->paiements()
            ->with('compte:id,nom,type')
            ->orderByDesc('date_paiement')
            ->orderByDesc('id')
            ->get();

        $montantPaye = (float) $facture->paiements()->sum('montant');
        $resteAPayer = max((float) $facture->total_facture - $montantPaye, 0);

        return $this->succes('Liste des paiements recuperee avec succes.', [
            'facture' => [
                'id' => $facture->id,
                'numero_facture' => $facture->numero_facture,
                'statut' => $facture->statut,
                'total_facture' => number_format((float) $facture->total_facture, 2, '.', ''),
                'montant_paye' => number_format($montantPaye, 2, '.', ''),
                'reste_a_payer' => number_format($resteAPayer, 2, '.', ''),
            ],
            'paiements' => PaiementResource::collection($paiements)->resolve(),
        ]);
    }

    public function store(PaiementStoreRequest $request, Facture $facture): JsonResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return $this->echec(
                'Paiement refuse. La facture est annulee.',
                ['facture' => ['Impossible d\'ajouter un paiement sur une facture annulee.']],
                409
            );
        }

        $payload = $request->validated();

        $paiement = DB::transaction(function () use ($payload, $facture): Paiement {
            $paiement = $facture->paiements()->create($payload);
            $this->factureService->recalculerStatut($facture);

            return $paiement;
        });

        $paiement->load('compte:id,nom,type');
        $facture->refresh();

        $montantPaye = (float) $facture->paiements()->sum('montant');
        $resteAPayer = max((float) $facture->total_facture - $montantPaye, 0);

        return $this->succes('Paiement ajoute avec succes.', [
            'paiement' => (new PaiementResource($paiement))->resolve(),
            'facture' => [
                'id' => $facture->id,
                'numero_facture' => $facture->numero_facture,
                'statut' => $facture->statut,
                'total_facture' => number_format((float) $facture->total_facture, 2, '.', ''),
                'montant_paye' => number_format($montantPaye, 2, '.', ''),
                'reste_a_payer' => number_format($resteAPayer, 2, '.', ''),
            ],
        ], 201);
    }

    public function show(Paiement $paiement): JsonResponse
    {
        $paiement->load(['compte:id,nom,type', 'facture:id,numero_facture,statut,total_facture']);

        return $this->succes('Paiement recupere avec succes.', (new PaiementResource($paiement))->resolve());
    }

    public function update(PaiementUpdateRequest $request, Paiement $paiement): JsonResponse
    {
        $facture = $paiement->facture;

        if ($facture->statut === 'ANNULEE') {
            return $this->echec(
                'Modification refusee. La facture est annulee.',
                ['facture' => ['Impossible de modifier un paiement lie a une facture annulee.']],
                409
            );
        }

        $payload = $request->validated();

        DB::transaction(function () use ($paiement, $payload, $facture): void {
            $paiement->update($payload);
            $this->factureService->recalculerStatut($facture);
        });

        $paiement->refresh();
        $paiement->load(['compte:id,nom,type', 'facture:id,numero_facture,statut,total_facture']);
        $facture->refresh();

        $montantPaye = (float) $facture->paiements()->sum('montant');
        $resteAPayer = max((float) $facture->total_facture - $montantPaye, 0);

        return $this->succes('Paiement mis a jour avec succes.', [
            'paiement' => (new PaiementResource($paiement))->resolve(),
            'facture' => [
                'id' => $facture->id,
                'numero_facture' => $facture->numero_facture,
                'statut' => $facture->statut,
                'total_facture' => number_format((float) $facture->total_facture, 2, '.', ''),
                'montant_paye' => number_format($montantPaye, 2, '.', ''),
                'reste_a_payer' => number_format($resteAPayer, 2, '.', ''),
            ],
        ]);
    }

    public function destroy(Paiement $paiement): JsonResponse
    {
        $facture = $paiement->facture;

        try {
            DB::transaction(function () use ($paiement, $facture): void {
                $paiement->delete();
                $this->factureService->recalculerStatut($facture);
            });
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer ce paiement.',
                ['suppression' => ['Une erreur est survenue pendant la suppression du paiement.']],
                409
            );
        }

        $facture->refresh();
        $montantPaye = (float) $facture->paiements()->sum('montant');
        $resteAPayer = max((float) $facture->total_facture - $montantPaye, 0);

        return $this->succes('Paiement supprime avec succes.', [
            'facture' => [
                'id' => $facture->id,
                'numero_facture' => $facture->numero_facture,
                'statut' => $facture->statut,
                'total_facture' => number_format((float) $facture->total_facture, 2, '.', ''),
                'montant_paye' => number_format($montantPaye, 2, '.', ''),
                'reste_a_payer' => number_format($resteAPayer, 2, '.', ''),
            ],
        ]);
    }
}

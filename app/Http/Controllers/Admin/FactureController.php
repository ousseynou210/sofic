<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Commercial;
use App\Models\Facture;
use App\Models\PointVente;
use App\Models\Produit;
use App\Services\FactureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FactureController extends Controller
{
    public function __construct(private readonly FactureService $factureService)
    {
    }

    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('recherche', ''));

        $query = Facture::query()
            ->with(['client:id,nom'])
            ->withSum('paiements as montant_paye', 'montant')
            ->orderByDesc('id');

        if ($recherche !== '') {
            $query->where('numero_facture', 'like', "%{$recherche}%");
        }

        if ($request->filled('statut')) {
            $query->where('statut', (string) $request->query('statut'));
        }

        $factures = $query->paginate(15)->withQueryString();

        return view('admin.factures.index', compact('factures', 'recherche'));
    }

    public function create(): View
    {
        [$clients, $produits, $commerciaux, $pointsVente] = $this->chargerReferentiels();

        return view('admin.factures.create', compact('clients', 'produits', 'commerciaux', 'pointsVente'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'date_emission' => ['required', 'date'],
            'date_echeance' => ['nullable', 'date', 'after_or_equal:date_emission'],
            'commercial_id' => ['nullable', 'integer', 'exists:commerciaux,id'],
            'point_vente_id' => ['nullable', 'integer', 'exists:points_vente,id'],
            'notes' => ['nullable', 'string'],
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.produit_id' => ['nullable', 'integer', 'exists:produits,id'],
            'lignes.*.description' => ['nullable', 'string', 'max:255'],
            'lignes.*.quantite' => ['required', 'integer', 'min:1'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
        ]);

        $facture = DB::transaction(function () use ($payload): Facture {
            $facture = Facture::create([
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
                $prix = (float) $ligne['prix_unitaire'];

                $facture->factureLignes()->create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'description' => $ligne['description'] ?? null,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prix,
                    'total_ligne' => $quantite * $prix,
                ]);
            }

            $this->factureService->decrementerStockDepuisLignes($facture);
            $this->factureService->recalculerTotaux($facture);
            $this->factureService->recalculerStatut($facture);

            return $facture;
        });

        return redirect()->route('admin.factures.show', $facture)->with('succes', 'Facture creee avec succes.');
    }

    public function show(Facture $facture): View
    {
        $facture->load([
            'client',
            'commercial',
            'pointVente',
            'factureLignes.produit',
            'paiements.compte',
        ])->loadSum('paiements as montant_paye', 'montant');

        $total = (float) $facture->total_facture;
        $paye = (float) ($facture->montant_paye ?? 0);
        $reste = max($total - $paye, 0);

        $comptes = \App\Models\Compte::orderBy('nom')->get();

        return view('admin.factures.show', compact('facture', 'total', 'paye', 'reste', 'comptes'));
    }

    public function edit(Facture $facture): View|RedirectResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return redirect()->route('admin.factures.show', $facture)->withErrors([
                'facture' => 'Impossible de modifier une facture annulee.',
            ]);
        }

        $facture->load('factureLignes');
        [$clients, $produits, $commerciaux, $pointsVente] = $this->chargerReferentiels();

        return view('admin.factures.edit', compact('facture', 'clients', 'produits', 'commerciaux', 'pointsVente'));
    }

    public function update(Request $request, Facture $facture): RedirectResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return back()->withErrors([
                'facture' => 'Impossible de modifier une facture annulee.',
            ]);
        }

        $payload = $request->validate([
            'numero_facture' => [
                'required',
                'string',
                'max:255',
                Rule::unique('factures', 'numero_facture')->ignore($facture->id),
            ],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'date_emission' => ['required', 'date'],
            'date_echeance' => ['nullable', 'date', 'after_or_equal:date_emission'],
            'commercial_id' => ['nullable', 'integer', 'exists:commerciaux,id'],
            'point_vente_id' => ['nullable', 'integer', 'exists:points_vente,id'],
            'notes' => ['nullable', 'string'],
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.produit_id' => ['nullable', 'integer', 'exists:produits,id'],
            'lignes.*.description' => ['nullable', 'string', 'max:255'],
            'lignes.*.quantite' => ['required', 'integer', 'min:1'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($facture, $payload): void {
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
                $prix = (float) $ligne['prix_unitaire'];

                $facture->factureLignes()->create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'description' => $ligne['description'] ?? null,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prix,
                    'total_ligne' => $quantite * $prix,
                ]);
            }

            $this->factureService->decrementerStockDepuisLignes($facture);
            $this->factureService->recalculerTotaux($facture);
            $this->factureService->recalculerStatut($facture);
        });

        return redirect()->route('admin.factures.show', $facture)->with('succes', 'Facture mise a jour avec succes.');
    }

    public function annuler(Facture $facture): RedirectResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return back()->withErrors(['facture' => 'La facture est deja annulee.']);
        }

        DB::transaction(function () use ($facture): void {
            $this->factureService->incrementerStockDepuisLignes($facture);
            $facture->update(['statut' => 'ANNULEE']);
        });

        return redirect()->route('admin.factures.show', $facture)->with('succes', 'Facture annulee avec succes.');
    }

    public function destroy(Facture $facture): RedirectResponse
    {
        if ($facture->statut !== 'BROUILLON') {
            return back()->withErrors([
                'facture' => 'Suppression interdite. Seules les factures BROUILLON peuvent etre supprimees.',
            ]);
        }

        if ($facture->paiements()->exists()) {
            return back()->withErrors([
                'facture' => 'Suppression interdite. Retirez d abord les paiements lies a la facture.',
            ]);
        }

        DB::transaction(function () use ($facture): void {
            $this->factureService->incrementerStockDepuisLignes($facture);
            $facture->delete();
        });

        return redirect()->route('admin.factures.index')->with('succes', 'Facture supprimee avec succes.');
    }

    private function chargerReferentiels(): array
    {
        $clients = Client::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();
        $commerciaux = Commercial::orderBy('nom')->get();
        $pointsVente = PointVente::orderBy('nom')->get();

        return [$clients, $produits, $commerciaux, $pointsVente];
    }
}

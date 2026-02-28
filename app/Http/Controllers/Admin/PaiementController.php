<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Paiement;
use App\Services\FactureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaiementController extends Controller
{
    public function __construct(private readonly FactureService $factureService)
    {
    }

    public function store(Request $request, Facture $facture): RedirectResponse
    {
        if ($facture->statut === 'ANNULEE') {
            return back()->withErrors([
                'montant' => 'Paiement refuse. La facture est annulee.',
            ]);
        }

        $data = $request->validate([
            'compte_id' => ['required', 'integer', 'exists:comptes,id'],
            'date_paiement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'mode' => ['required', Rule::in(['ESPECES', 'WAVE', 'ORANGE_MONEY', 'VIREMENT', 'CHEQUE'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $facture): void {
            $facture->paiements()->create($data);
            $this->factureService->recalculerStatut($facture);
        });

        return redirect()->route('admin.factures.show', $facture)->with('succes', 'Paiement ajoute avec succes.');
    }

    public function destroy(Paiement $paiement): RedirectResponse
    {
        $facture = $paiement->facture;

        DB::transaction(function () use ($paiement, $facture): void {
            $paiement->delete();
            $this->factureService->recalculerStatut($facture);
        });

        return redirect()->route('admin.factures.show', $facture)->with('succes', 'Paiement supprime avec succes.');
    }
}

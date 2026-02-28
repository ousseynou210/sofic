<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\Produit;
use Carbon\Carbon;

class FactureService
{
    public function genererNumeroFacture(string $date): string
    {
        $annee = Carbon::parse($date)->format('Y');
        $prefixe = "FAC-{$annee}-";

        $dernierNumero = Facture::query()
            ->where('numero_facture', 'like', "{$prefixe}%")
            ->orderByDesc('numero_facture')
            ->value('numero_facture');

        $sequence = $dernierNumero ? ((int) substr($dernierNumero, -6) + 1) : 1;

        do {
            $numero = $prefixe . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Facture::query()->where('numero_facture', $numero)->exists());

        return $numero;
    }

    public function recalculerTotaux(Facture $facture): Facture
    {
        $total = (float) $facture->factureLignes()->sum('total_ligne');

        $facture->total_facture = $total;
        $facture->save();

        return $facture;
    }

    public function recalculerStatut(Facture $facture): Facture
    {
        if ($facture->statut === 'ANNULEE') {
            return $facture;
        }

        $totalFacture = (float) $facture->total_facture;
        $montantPaye = (float) $facture->paiements()->sum('montant');

        if ($montantPaye <= 0) {
            $nouveauStatut = $facture->statut === 'BROUILLON' ? 'BROUILLON' : 'ENVOYEE';
        } elseif ($montantPaye < $totalFacture) {
            $nouveauStatut = 'PARTIELLE';
        } else {
            $nouveauStatut = 'PAYEE';
        }

        if ($facture->statut !== $nouveauStatut) {
            $facture->statut = $nouveauStatut;
            $facture->save();
        }

        return $facture;
    }

    public function decrementerStockDepuisLignes(Facture $facture): void
    {
        $this->ajusterStockDepuisLignes($facture, -1);
    }

    public function incrementerStockDepuisLignes(Facture $facture): void
    {
        $this->ajusterStockDepuisLignes($facture, 1);
    }

    private function ajusterStockDepuisLignes(Facture $facture, int $sens): void
    {
        $mouvements = $facture->factureLignes()
            ->selectRaw('produit_id, SUM(quantite) as quantite')
            ->whereNotNull('produit_id')
            ->groupBy('produit_id')
            ->get();

        foreach ($mouvements as $mouvement) {
            $produitId = (int) $mouvement->produit_id;
            $quantite = (int) $mouvement->quantite;

            if ($produitId <= 0 || $quantite <= 0) {
                continue;
            }

            if ($sens > 0) {
                Produit::query()->whereKey($produitId)->increment('stock_qty', $quantite);
                continue;
            }

            Produit::query()->whereKey($produitId)->decrement('stock_qty', $quantite);
        }
    }
}

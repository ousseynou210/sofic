<?php

namespace App\Services;

use App\Models\Compte;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TableauDeBordService
{
    public function construireResume(string $periode): array
    {
        $periode = $this->normaliserPeriode($periode);
        [$dateDebut, $dateFin] = $this->intervallePeriode($periode);

        $chiffreAffaires = (float) DB::table('factures')
            ->where('statut', '!=', 'ANNULEE')
            ->whereBetween('date_emission', [$dateDebut, $dateFin])
            ->sum('total_facture');

        $encaisse = (float) DB::table('paiements')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('montant');

        $depenses = (float) DB::table('depenses')
            ->whereBetween('date_depense', [$dateDebut, $dateFin])
            ->sum('montant');

        $impayes = (float) $this->calculerImpayes();

        $soldesComptes = $this->calculerSoldesComptes();

        return [
            'periode' => $periode,
            'intervalle' => [
                'du' => $dateDebut->toDateString(),
                'au' => $dateFin->toDateString(),
            ],
            'chiffre_affaires' => $chiffreAffaires,
            'encaisse' => $encaisse,
            'depenses' => $depenses,
            'impayes' => $impayes,
            'soldes_comptes' => $soldesComptes,
            'graphiques' => [
                'ca_par_mois' => $this->caParMois(),
                'depenses_par_categorie' => $this->depensesParCategorie($dateDebut, $dateFin),
                'top_clients' => $this->topClients($dateDebut, $dateFin),
                'top_produits' => $this->topProduits($dateDebut, $dateFin),
            ],
        ];
    }

    public function normaliserPeriode(string $periode): string
    {
        $periode = strtolower(trim($periode));
        $valides = ['mois', 'trimestre', 'annee'];

        return in_array($periode, $valides, true) ? $periode : 'mois';
    }

    private function intervallePeriode(string $periode): array
    {
        $maintenant = Carbon::now();

        return match ($periode) {
            'annee' => [$maintenant->copy()->startOfYear(), $maintenant->copy()->endOfYear()],
            'trimestre' => [$maintenant->copy()->firstOfQuarter(), $maintenant->copy()->lastOfQuarter()],
            default => [$maintenant->copy()->startOfMonth(), $maintenant->copy()->endOfMonth()],
        };
    }

    private function calculerImpayes(): float
    {
        $sousQueryPaiements = DB::table('paiements')
            ->selectRaw('facture_id, SUM(montant) as montant_paye')
            ->groupBy('facture_id');

        $connection = DB::connection()->getDriverName();
        $expressionImpayes = $connection === 'sqlite'
            ? 'COALESCE(SUM(CASE WHEN (f.total_facture - COALESCE(p.montant_paye, 0)) > 0 THEN (f.total_facture - COALESCE(p.montant_paye, 0)) ELSE 0 END), 0) as total_impayes'
            : 'COALESCE(SUM(GREATEST(f.total_facture - COALESCE(p.montant_paye, 0), 0)), 0) as total_impayes';

        return (float) DB::table('factures as f')
            ->leftJoinSub($sousQueryPaiements, 'p', 'p.facture_id', '=', 'f.id')
            ->whereNotIn('f.statut', ['PAYEE', 'ANNULEE'])
            ->selectRaw($expressionImpayes)
            ->value('total_impayes');
    }

    private function calculerSoldesComptes(): array
    {
        $comptes = Compte::query()
            ->select(['id', 'nom', 'type', 'solde_initial'])
            ->withSum('paiements as total_entrees', 'montant')
            ->withSum('depenses as total_sorties', 'montant')
            ->orderBy('nom')
            ->get();

        return $comptes->map(function (Compte $compte): array {
            $entrees = (float) ($compte->total_entrees ?? 0);
            $sorties = (float) ($compte->total_sorties ?? 0);
            $soldeInitial = (float) $compte->solde_initial;

            return [
                'id' => $compte->id,
                'nom' => $compte->nom,
                'type' => $compte->type,
                'solde_initial' => $soldeInitial,
                'entrees' => $entrees,
                'sorties' => $sorties,
                'solde' => $soldeInitial + $entrees - $sorties,
            ];
        })->values()->all();
    }

    private function caParMois(): array
    {
        $debut = Carbon::now()->startOfMonth()->subMonths(11);
        $fin = Carbon::now()->endOfMonth();
        $connection = DB::connection()->getDriverName();
        $expressionMois = $connection === 'sqlite'
            ? "strftime('%Y-%m', date_emission)"
            : "DATE_FORMAT(date_emission, '%Y-%m')";

        $resultats = DB::table('factures')
            ->selectRaw("$expressionMois as mois, SUM(total_facture) as total")
            ->where('statut', '!=', 'ANNULEE')
            ->whereBetween('date_emission', [$debut, $fin])
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $labels = [];
        $valeurs = [];

        $cursor = $debut->copy();
        while ($cursor->lte($fin)) {
            $cle = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $valeurs[] = (float) ($resultats[$cle] ?? 0);
            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'valeurs' => $valeurs,
        ];
    }

    private function depensesParCategorie(Carbon $du, Carbon $au): array
    {
        $lignes = DB::table('depenses')
            ->selectRaw('categorie, SUM(montant) as total')
            ->whereBetween('date_depense', [$du, $au])
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $lignes->pluck('categorie')->values()->all(),
            'valeurs' => $lignes->pluck('total')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    private function topClients(Carbon $du, Carbon $au): array
    {
        return DB::table('factures as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->selectRaw('c.id, c.nom, SUM(f.total_facture) as chiffre_affaires')
            ->where('f.statut', '!=', 'ANNULEE')
            ->whereBetween('f.date_emission', [$du, $au])
            ->groupBy('c.id', 'c.nom')
            ->orderByDesc('chiffre_affaires')
            ->limit(5)
            ->get()
            ->map(fn ($ligne) => [
                'id' => (int) $ligne->id,
                'nom' => $ligne->nom,
                'chiffre_affaires' => (float) $ligne->chiffre_affaires,
            ])
            ->values()
            ->all();
    }

    private function topProduits(Carbon $du, Carbon $au): array
    {
        return DB::table('facture_lignes as fl')
            ->join('factures as f', 'f.id', '=', 'fl.facture_id')
            ->leftJoin('produits as p', 'p.id', '=', 'fl.produit_id')
            ->selectRaw("COALESCE(p.nom, fl.description, 'Sans designation') as nom_produit, SUM(fl.quantite) as quantite_vendue")
            ->where('f.statut', '!=', 'ANNULEE')
            ->whereBetween('f.date_emission', [$du, $au])
            ->groupBy('nom_produit')
            ->orderByDesc('quantite_vendue')
            ->limit(5)
            ->get()
            ->map(fn ($ligne) => [
                'nom' => $ligne->nom_produit,
                'quantite_vendue' => (int) $ligne->quantite_vendue,
            ])
            ->values()
            ->all();
    }
}

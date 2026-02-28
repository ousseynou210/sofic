<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompteSoldeController extends Controller
{
    use ApiResponse;

    public function show(Compte $compte): JsonResponse
    {
        $compte->loadSum('paiements as total_entrees', 'montant')
            ->loadSum('depenses as total_sorties', 'montant');

        $entrees = (float) ($compte->total_entrees ?? 0);
        $sorties = (float) ($compte->total_sorties ?? 0);
        $soldeInitial = (float) $compte->solde_initial;
        $solde = $soldeInitial + $entrees - $sorties;

        return $this->succes('Solde du compte recupere avec succes.', [
            'id' => $compte->id,
            'nom' => $compte->nom,
            'type' => $compte->type,
            'solde_initial' => number_format($soldeInitial, 2, '.', ''),
            'entrees' => number_format($entrees, 2, '.', ''),
            'sorties' => number_format($sorties, 2, '.', ''),
            'solde' => number_format($solde, 2, '.', ''),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $comptes = Compte::query()
            ->select(['id', 'nom', 'type', 'solde_initial'])
            ->withSum('paiements as total_entrees', 'montant')
            ->withSum('depenses as total_sorties', 'montant')
            ->orderBy('nom')
            ->get();

        $donnees = $comptes->map(function (Compte $compte): array {
            $entrees = (float) ($compte->total_entrees ?? 0);
            $sorties = (float) ($compte->total_sorties ?? 0);
            $soldeInitial = (float) $compte->solde_initial;
            $solde = $soldeInitial + $entrees - $sorties;

            return [
                'id' => $compte->id,
                'nom' => $compte->nom,
                'type' => $compte->type,
                'solde_initial' => number_format($soldeInitial, 2, '.', ''),
                'entrees' => number_format($entrees, 2, '.', ''),
                'sorties' => number_format($sorties, 2, '.', ''),
                'solde' => number_format($solde, 2, '.', ''),
            ];
        })->values();

        return $this->succes('Soldes des comptes recuperes avec succes.', $donnees);
    }
}

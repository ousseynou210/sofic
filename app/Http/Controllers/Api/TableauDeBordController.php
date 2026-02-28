<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\TableauDeBordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableauDeBordController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TableauDeBordService $tableauDeBordService)
    {
    }

    public function resume(Request $request): JsonResponse
    {
        $periode = (string) $request->query('periode', 'mois');
        $periodeNormalisee = strtolower(trim($periode));
        $valides = ['mois', 'trimestre', 'annee'];

        if (! in_array($periodeNormalisee, $valides, true)) {
            return $this->echec(
                'Erreurs de validation.',
                ['periode' => ['La periode doit etre: mois, trimestre ou annee.']],
                422
            );
        }

        $donnees = $this->tableauDeBordService->construireResume($periodeNormalisee);

        return $this->succes('Resume du tableau de bord recupere avec succes.', $donnees);
    }
}

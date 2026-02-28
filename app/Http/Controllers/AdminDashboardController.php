<?php

namespace App\Http\Controllers;

use App\Services\TableauDeBordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly TableauDeBordService $tableauDeBordService)
    {
    }

    public function index(Request $request): View
    {
        $periode = $this->tableauDeBordService->normaliserPeriode((string) $request->query('periode', 'mois'));
        $donnees = $this->tableauDeBordService->construireResume($periode);

        return view('admin.dashboard', [
            'periode' => $periode,
            'resume' => $donnees,
        ]);
    }
}

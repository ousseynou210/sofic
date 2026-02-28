<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Services\TableauDeBordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly TableauDeBordService $tableauDeBordService)
    {
    }

    public function index(Request $request): View
    {
        $periode = $this->tableauDeBordService->normaliserPeriode((string) $request->query('periode', 'mois'));
        $resume = $this->tableauDeBordService->construireResume($periode);
        $dernieresFactures = Facture::query()
            ->with(['client:id,nom'])
            ->withSum('paiements as montant_paye', 'montant')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('periode', 'resume', 'dernieresFactures'));
    }
}

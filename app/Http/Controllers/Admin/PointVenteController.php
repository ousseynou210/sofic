<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointVente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointVenteController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('recherche', ''));
        $query = PointVente::query();

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('adresse', 'like', "%{$recherche}%")
                    ->orWhere('telephone', 'like', "%{$recherche}%");
            });
        }

        $pointsVente = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.points_vente.index', compact('pointsVente', 'recherche'));
    }

    public function create(): View
    {
        return view('admin.points_vente.form', ['pointVente' => new PointVente()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
        ]);

        PointVente::create($data);

        return redirect()->route('admin.points-vente.index')->with('succes', 'Point de vente cree avec succes.');
    }

    public function edit(PointVente $points_vente): View
    {
        $pointVente = $points_vente;

        return view('admin.points_vente.form', compact('pointVente'));
    }

    public function update(Request $request, PointVente $points_vente): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
        ]);

        $points_vente->update($data);

        return redirect()->route('admin.points-vente.index')->with('succes', 'Point de vente mis a jour avec succes.');
    }

    public function destroy(PointVente $points_vente): RedirectResponse
    {
        $points_vente->delete();

        return redirect()->route('admin.points-vente.index')->with('succes', 'Point de vente supprime avec succes.');
    }
}

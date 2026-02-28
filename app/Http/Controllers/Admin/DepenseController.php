<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Depense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepenseController extends Controller
{
    public function index(Request $request): View
    {
        $du = $request->query('du');
        $au = $request->query('au');
        $categorie = trim((string) $request->query('categorie', ''));
        $compteId = $request->query('compte_id');

        $query = Depense::query()->with('compte')->orderByDesc('date_depense')->orderByDesc('id');

        if ($du) {
            $query->whereDate('date_depense', '>=', $du);
        }
        if ($au) {
            $query->whereDate('date_depense', '<=', $au);
        }
        if ($categorie !== '') {
            $query->where('categorie', 'like', "%{$categorie}%");
        }
        if ($compteId) {
            $query->where('compte_id', (int) $compteId);
        }

        $depenses = $query->paginate(15)->withQueryString();
        $comptes = Compte::orderBy('nom')->get();

        return view('admin.depenses.index', compact('depenses', 'comptes', 'du', 'au', 'categorie', 'compteId'));
    }

    public function create(): View
    {
        $depense = new Depense();
        $comptes = Compte::orderBy('nom')->get();

        return view('admin.depenses.form', compact('depense', 'comptes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->valider($request);
        Depense::create($data);

        return redirect()->route('admin.depenses.index')->with('succes', 'Depense creee avec succes.');
    }

    public function edit(Depense $depense): View
    {
        $comptes = Compte::orderBy('nom')->get();

        return view('admin.depenses.form', compact('depense', 'comptes'));
    }

    public function update(Request $request, Depense $depense): RedirectResponse
    {
        $data = $this->valider($request);
        $depense->update($data);

        return redirect()->route('admin.depenses.index')->with('succes', 'Depense mise a jour avec succes.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        $depense->delete();

        return redirect()->route('admin.depenses.index')->with('succes', 'Depense supprimee avec succes.');
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'compte_id' => ['required', 'integer', 'exists:comptes,id'],
            'date_depense' => ['required', 'date'],
            'categorie' => ['required', 'string', 'max:255'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'mode' => ['required', Rule::in(['ESPECES', 'WAVE', 'ORANGE_MONEY', 'VIREMENT', 'CHEQUE'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);
    }
}

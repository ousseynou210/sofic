<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompteController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('recherche', ''));
        $query = Compte::query()
            ->withSum('paiements as total_entrees', 'montant')
            ->withSum('depenses as total_sorties', 'montant');

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('type', 'like', "%{$recherche}%");
            });
        }

        $comptes = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.comptes.index', compact('comptes', 'recherche'));
    }

    public function create(): View
    {
        return view('admin.comptes.form', ['compte' => new Compte()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['CAISSE', 'BANQUE', 'MOBILE_MONEY', 'AUTRE'])],
            'solde_initial' => ['required', 'numeric'],
        ]);

        Compte::create($data);

        return redirect()->route('admin.comptes.index')->with('succes', 'Compte cree avec succes.');
    }

    public function edit(Compte $compte): View
    {
        return view('admin.comptes.form', compact('compte'));
    }

    public function update(Request $request, Compte $compte): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['CAISSE', 'BANQUE', 'MOBILE_MONEY', 'AUTRE'])],
            'solde_initial' => ['required', 'numeric'],
        ]);

        $compte->update($data);

        return redirect()->route('admin.comptes.index')->with('succes', 'Compte mis a jour avec succes.');
    }

    public function destroy(Compte $compte): RedirectResponse
    {
        $compte->delete();

        return redirect()->route('admin.comptes.index')->with('succes', 'Compte supprime avec succes.');
    }
}

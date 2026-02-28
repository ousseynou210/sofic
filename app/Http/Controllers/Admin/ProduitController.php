<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduitController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('recherche', ''));
        $query = Produit::query();

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('categorie', 'like', "%{$recherche}%");
            });
        }

        $produits = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.produits.index', compact('produits', 'recherche'));
    }

    public function create(): View
    {
        return view('admin.produits.form', ['produit' => new Produit()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['required', 'integer', 'min:0'],
        ]);

        Produit::create($data);

        return redirect()->route('admin.produits.index')->with('succes', 'Produit cree avec succes.');
    }

    public function edit(Produit $produit): View
    {
        return view('admin.produits.form', compact('produit'));
    }

    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['required', 'integer', 'min:0'],
        ]);

        $produit->update($data);

        return redirect()->route('admin.produits.index')->with('succes', 'Produit mis a jour avec succes.');
    }

    public function destroy(Produit $produit): RedirectResponse
    {
        $produit->delete();

        return redirect()->route('admin.produits.index')->with('succes', 'Produit supprime avec succes.');
    }
}

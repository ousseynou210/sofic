<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commercial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('recherche', ''));
        $query = Commercial::query();

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('telephone', 'like', "%{$recherche}%")
                    ->orWhere('email', 'like', "%{$recherche}%");
            });
        }

        $commerciaux = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.commerciaux.index', compact('commerciaux', 'recherche'));
    }

    public function create(): View
    {
        return view('admin.commerciaux.form', ['commercial' => new Commercial()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        Commercial::create($data);

        return redirect()->route('admin.commerciaux.index')->with('succes', 'Commercial cree avec succes.');
    }

    public function edit(Commercial $commercial): View
    {
        return view('admin.commerciaux.form', compact('commercial'));
    }

    public function update(Request $request, Commercial $commercial): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $commercial->update($data);

        return redirect()->route('admin.commerciaux.index')->with('succes', 'Commercial mis a jour avec succes.');
    }

    public function destroy(Commercial $commercial): RedirectResponse
    {
        $commercial->delete();

        return redirect()->route('admin.commerciaux.index')->with('succes', 'Commercial supprime avec succes.');
    }
}

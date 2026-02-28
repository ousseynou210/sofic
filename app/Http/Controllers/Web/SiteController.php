<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function accueil(): View
    {
        $produits = Produit::query()
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('site.accueil', compact('produits'));
    }

    public function produits(Request $request): View
    {
        $recherche = trim((string) $request->query('recherche', ''));
        $query = Produit::query()->orderByDesc('id');

        if ($recherche !== '') {
            $query->where('nom', 'like', "%{$recherche}%")
                ->orWhere('categorie', 'like', "%{$recherche}%");
        }

        $produits = $query->paginate(12)->withQueryString();

        return view('site.produits.index', compact('produits', 'recherche'));
    }

    public function suiviFactureForm(): View
    {
        return view('site.factures.suivi');
    }

    public function suiviFactureResultat(Request $request): View
    {
        $donnees = $request->validate([
            'numero_facture' => ['required', 'string', 'max:255'],
            'identifiant_client' => ['required', 'string', 'max:255'],
        ], [
            'numero_facture.required' => 'Le numero de facture est obligatoire.',
            'identifiant_client.required' => 'L email ou le telephone client est obligatoire.',
        ]);

        $facture = Facture::query()
            ->with(['client:id,nom,email,telephone', 'factureLignes.produit:id,nom'])
            ->withSum('paiements as montant_paye', 'montant')
            ->where('numero_facture', $donnees['numero_facture'])
            ->whereHas('client', function ($query) use ($donnees): void {
                $query->where('email', $donnees['identifiant_client'])
                    ->orWhere('telephone', $donnees['identifiant_client']);
            })
            ->first();

        return view('site.factures.suivi', [
            'facture' => $facture,
            'numeroFacture' => $donnees['numero_facture'],
            'identifiantClient' => $donnees['identifiant_client'],
        ]);
    }
}

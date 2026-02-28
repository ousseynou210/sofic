<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProduitRequest;
use App\Http\Resources\ProduitResource;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProduitController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));
        $recherche = trim((string) $request->query('recherche', ''));

        $query = Produit::query();

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('categorie', 'like', "%{$recherche}%");
            });
        }

        $paginator = $query->orderByDesc('id')->paginate($parPage)->withQueryString();
        $elements = ProduitResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des produits recuperee avec succes.', $paginator, $elements);
    }

    public function store(ProduitRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['stock_qty'] ??= 0;

        $produit = Produit::create($payload);

        return $this->succes('Produit cree avec succes.', (new ProduitResource($produit))->resolve(), 201);
    }

    public function show(Produit $produit): JsonResponse
    {
        return $this->succes('Produit recupere avec succes.', (new ProduitResource($produit))->resolve());
    }

    public function update(ProduitRequest $request, Produit $produit): JsonResponse
    {
        $payload = $request->validated();
        $payload['stock_qty'] ??= 0;

        $produit->update($payload);

        return $this->succes('Produit mis a jour avec succes.', (new ProduitResource($produit->fresh()))->resolve());
    }

    public function destroy(Produit $produit): JsonResponse
    {
        try {
            $produit->delete();
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer ce produit.',
                ['suppression' => ['Ce produit est lie a des enregistrements existants.']],
                409
            );
        }

        return $this->succes('Produit supprime avec succes.');
    }
}

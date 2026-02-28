<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PointVenteRequest;
use App\Http\Resources\PointVenteResource;
use App\Models\PointVente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PointVenteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));
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

        $paginator = $query->orderByDesc('id')->paginate($parPage)->withQueryString();
        $elements = PointVenteResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des points de vente recuperee avec succes.', $paginator, $elements);
    }

    public function store(PointVenteRequest $request): JsonResponse
    {
        $pointVente = PointVente::create($request->validated());

        return $this->succes('Point de vente cree avec succes.', (new PointVenteResource($pointVente))->resolve(), 201);
    }

    public function show(PointVente $point_vente): JsonResponse
    {
        return $this->succes('Point de vente recupere avec succes.', (new PointVenteResource($point_vente))->resolve());
    }

    public function update(PointVenteRequest $request, PointVente $point_vente): JsonResponse
    {
        $point_vente->update($request->validated());

        return $this->succes('Point de vente mis a jour avec succes.', (new PointVenteResource($point_vente->fresh()))->resolve());
    }

    public function destroy(PointVente $point_vente): JsonResponse
    {
        try {
            $point_vente->delete();
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer ce point de vente.',
                ['suppression' => ['Ce point de vente est lie a des enregistrements existants.']],
                409
            );
        }

        return $this->succes('Point de vente supprime avec succes.');
    }
}

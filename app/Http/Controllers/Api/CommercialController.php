<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CommercialRequest;
use App\Http\Resources\CommercialResource;
use App\Models\Commercial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CommercialController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));
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

        $paginator = $query->orderByDesc('id')->paginate($parPage)->withQueryString();
        $elements = CommercialResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des commerciaux recuperee avec succes.', $paginator, $elements);
    }

    public function store(CommercialRequest $request): JsonResponse
    {
        $commercial = Commercial::create($request->validated());

        return $this->succes('Commercial cree avec succes.', (new CommercialResource($commercial))->resolve(), 201);
    }

    public function show(Commercial $commercial): JsonResponse
    {
        return $this->succes('Commercial recupere avec succes.', (new CommercialResource($commercial))->resolve());
    }

    public function update(CommercialRequest $request, Commercial $commercial): JsonResponse
    {
        $commercial->update($request->validated());

        return $this->succes('Commercial mis a jour avec succes.', (new CommercialResource($commercial->fresh()))->resolve());
    }

    public function destroy(Commercial $commercial): JsonResponse
    {
        try {
            $commercial->delete();
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer ce commercial.',
                ['suppression' => ['Ce commercial est lie a des enregistrements existants.']],
                409
            );
        }

        return $this->succes('Commercial supprime avec succes.');
    }
}

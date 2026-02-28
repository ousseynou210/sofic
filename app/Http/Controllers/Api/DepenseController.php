<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DepenseRequest;
use App\Http\Resources\DepenseResource;
use App\Models\Depense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DepenseController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));

        $query = Depense::query()
            ->with('compte:id,nom,type')
            ->orderByDesc('date_depense')
            ->orderByDesc('id');

        if ($request->filled('du')) {
            $query->whereDate('date_depense', '>=', (string) $request->query('du'));
        }

        if ($request->filled('au')) {
            $query->whereDate('date_depense', '<=', (string) $request->query('au'));
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', 'like', '%' . trim((string) $request->query('categorie')) . '%');
        }

        if ($request->filled('compte_id')) {
            $query->where('compte_id', (int) $request->query('compte_id'));
        }

        $paginator = $query->paginate($parPage)->withQueryString();
        $elements = DepenseResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des depenses recuperee avec succes.', $paginator, $elements);
    }

    public function store(DepenseRequest $request): JsonResponse
    {
        $depense = Depense::create($request->validated());
        $depense->load('compte:id,nom,type');

        return $this->succes('Depense creee avec succes.', (new DepenseResource($depense))->resolve(), 201);
    }

    public function show(Depense $depense): JsonResponse
    {
        $depense->load('compte:id,nom,type');

        return $this->succes('Depense recuperee avec succes.', (new DepenseResource($depense))->resolve());
    }

    public function update(DepenseRequest $request, Depense $depense): JsonResponse
    {
        $depense->update($request->validated());
        $depense->load('compte:id,nom,type');

        return $this->succes('Depense mise a jour avec succes.', (new DepenseResource($depense))->resolve());
    }

    public function destroy(Depense $depense): JsonResponse
    {
        try {
            $depense->delete();
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer cette depense.',
                ['suppression' => ['Une erreur est survenue pendant la suppression.']],
                409
            );
        }

        return $this->succes('Depense supprimee avec succes.');
    }
}

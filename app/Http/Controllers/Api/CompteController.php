<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CompteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));
        $recherche = trim((string) $request->query('recherche', ''));

        $query = Compte::query();

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('type', 'like', "%{$recherche}%");
            });
        }

        $paginator = $query->orderByDesc('id')->paginate($parPage)->withQueryString();
        $elements = CompteResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des comptes recuperee avec succes.', $paginator, $elements);
    }

    public function store(CompteRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['solde_initial'] ??= 0;

        $compte = Compte::create($payload);

        return $this->succes('Compte cree avec succes.', (new CompteResource($compte))->resolve(), 201);
    }

    public function show(Compte $compte): JsonResponse
    {
        return $this->succes('Compte recupere avec succes.', (new CompteResource($compte))->resolve());
    }

    public function update(CompteRequest $request, Compte $compte): JsonResponse
    {
        $payload = $request->validated();
        $payload['solde_initial'] ??= 0;

        $compte->update($payload);

        return $this->succes('Compte mis a jour avec succes.', (new CompteResource($compte->fresh()))->resolve());
    }

    public function destroy(Compte $compte): JsonResponse
    {
        try {
            $compte->delete();
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer ce compte.',
                ['suppression' => ['Ce compte est lie a des enregistrements existants.']],
                409
            );
        }

        return $this->succes('Compte supprime avec succes.');
    }
}

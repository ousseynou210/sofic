<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ClientController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $parPage = max(1, min(100, (int) $request->query('par_page', 15)));
        $recherche = trim((string) $request->query('recherche', ''));

        $query = Client::query();

        if ($recherche !== '') {
            $query->where(function ($builder) use ($recherche): void {
                $builder
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('telephone', 'like', "%{$recherche}%")
                    ->orWhere('email', 'like', "%{$recherche}%")
                    ->orWhere('adresse', 'like', "%{$recherche}%");
            });
        }

        $paginator = $query->orderByDesc('id')->paginate($parPage)->withQueryString();
        $elements = ClientResource::collection($paginator->getCollection())->resolve();

        return $this->reponsePaginee('Liste des clients recuperee avec succes.', $paginator, $elements);
    }

    public function store(ClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return $this->succes('Client cree avec succes.', (new ClientResource($client))->resolve(), 201);
    }

    public function show(Client $client): JsonResponse
    {
        return $this->succes('Client recupere avec succes.', (new ClientResource($client))->resolve());
    }

    public function update(ClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return $this->succes('Client mis a jour avec succes.', (new ClientResource($client->fresh()))->resolve());
    }

    public function destroy(Client $client): JsonResponse
    {
        try {
            $client->delete();
        } catch (Throwable $throwable) {
            return $this->echec(
                'Impossible de supprimer ce client.',
                ['suppression' => ['Ce client est lie a des enregistrements existants.']],
                409
            );
        }

        return $this->succes('Client supprime avec succes.');
    }
}

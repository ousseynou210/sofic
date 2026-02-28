<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function succes(string $message, mixed $donnees = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'succes' => true,
            'message' => $message,
            'donnees' => $donnees,
            'erreurs' => null,
        ], $status);
    }

    protected function echec(string $message, mixed $erreurs = null, int $status = 400, mixed $donnees = null): JsonResponse
    {
        return response()->json([
            'succes' => false,
            'message' => $message,
            'donnees' => $donnees,
            'erreurs' => $erreurs,
        ], $status);
    }

    protected function reponsePaginee(
        string $message,
        LengthAwarePaginator $paginator,
        array $elements
    ): JsonResponse {
        return $this->succes($message, [
            'elements' => $elements,
            'pagination' => [
                'page_actuelle' => $paginator->currentPage(),
                'par_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'derniere_page' => $paginator->lastPage(),
            ],
        ]);
    }
}

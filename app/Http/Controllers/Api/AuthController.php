<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function connexion(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'email' => $request->string('email')->toString(),
            'password' => $request->string('mot_de_passe')->toString(),
        ];

        if (! Auth::attempt($credentials)) {
            return $this->reponse(
                false,
                'Identifiants invalides.',
                null,
                ['identifiants' => ['Email ou mot de passe incorrect.']],
                401
            );
        }

        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return $this->reponse(
                false,
                'Acces refuse. Compte administrateur requis.',
                null,
                ['role' => ['Seul un administrateur peut se connecter a l\'API.']],
                403
            );
        }

        $request->session()->regenerate();

        return $this->reponse(true, 'Connexion reussie.', [
            'utilisateur' => [
                'id' => $user->id,
                'nom' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function deconnexion(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return $this->reponse(
                false,
                'Acces refuse. Compte administrateur requis.',
                null,
                ['role' => ['Seul un administrateur peut acceder a cette ressource.']],
                403
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->reponse(true, 'Deconnexion reussie.');
    }

    public function moi(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return $this->reponse(
                false,
                'Acces refuse. Compte administrateur requis.',
                null,
                ['role' => ['Seul un administrateur peut acceder a cette ressource.']],
                403
            );
        }

        return $this->reponse(true, 'Profil recupere avec succes.', [
            'id' => $user->id,
            'nom' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'cree_le' => $user->created_at,
            'mis_a_jour_le' => $user->updated_at,
        ]);
    }

    private function reponse(
        bool $succes,
        string $message,
        mixed $donnees = null,
        mixed $erreurs = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'succes' => $succes,
            'message' => $message,
            'donnees' => $donnees,
            'erreurs' => $erreurs,
        ], $status);
    }
}

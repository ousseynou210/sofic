<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return response()->json([
                'succes' => false,
                'message' => 'Acces refuse. Compte administrateur requis.',
                'donnees' => null,
                'erreurs' => [
                    'role' => ['Seul un administrateur peut acceder a cette ressource.'],
                ],
            ], 403);
        }

        return $next($request);
    }
}

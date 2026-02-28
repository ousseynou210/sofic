<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminWeb
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Vous devez vous connecter.',
            ]);
        }

        if ($user->role !== 'admin') {
            return redirect()->route('admin.dashboard')->withErrors([
                'acces' => 'Acces reserve aux administrateurs.',
            ]);
        }

        return $next($request);
    }
}

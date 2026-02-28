<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo('/admin/dashboard');

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'admin.web' => \App\Http\Middleware\EnsureAdminWeb::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'succes' => false,
                    'message' => 'Non authentifie.',
                    'donnees' => null,
                    'erreurs' => [
                        'auth' => ['Vous devez etre connecte pour acceder a cette ressource.'],
                    ],
                ], 401);
            }

            return null;
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'succes' => false,
                    'message' => 'Ressource introuvable.',
                    'donnees' => null,
                    'erreurs' => [
                        'ressource' => ['Aucun enregistrement ne correspond a votre demande.'],
                    ],
                ], 404);
            }

            return null;
        });
    })->create();

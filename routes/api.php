<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CommercialController;
use App\Http\Controllers\Api\CompteController;
use App\Http\Controllers\Api\CompteSoldeController;
use App\Http\Controllers\Api\DepenseController;
use App\Http\Controllers\Api\FactureController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\PointVenteController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\TableauDeBordController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::middleware('web')->group(function (): void {
        Route::post('/connexion', [AuthController::class, 'connexion'])->middleware('throttle:10,1');
        Route::post('/deconnexion', [AuthController::class, 'deconnexion'])->middleware(['auth', 'admin']);
        Route::get('/moi', [AuthController::class, 'moi'])->middleware(['auth', 'admin']);
    });
});

Route::middleware(['web', 'auth', 'admin'])->group(function (): void {
    Route::apiResource('clients', ClientController::class)->parameters(['clients' => 'client']);
    Route::apiResource('produits', ProduitController::class)->parameters(['produits' => 'produit']);
    Route::get('tableau-de-bord/resume', [TableauDeBordController::class, 'resume']);
    Route::get('comptes/soldes', [CompteSoldeController::class, 'index']);
    Route::get('comptes/{compte}/solde', [CompteSoldeController::class, 'show'])->whereNumber('compte');
    Route::apiResource('comptes', CompteController::class)->parameters(['comptes' => 'compte'])->whereNumber('compte');
    Route::apiResource('commerciaux', CommercialController::class)->parameters(['commerciaux' => 'commercial']);
    Route::apiResource('points-vente', PointVenteController::class)->parameters(['points-vente' => 'point_vente']);
    Route::apiResource('depenses', DepenseController::class)->parameters(['depenses' => 'depense'])->whereNumber('depense');
    Route::get('factures', [FactureController::class, 'index']);
    Route::post('factures', [FactureController::class, 'store']);
    Route::get('factures/{facture}', [FactureController::class, 'show']);
    Route::put('factures/{facture}', [FactureController::class, 'update']);
    Route::post('factures/{facture}/annuler', [FactureController::class, 'annuler']);
    Route::delete('factures/{facture}', [FactureController::class, 'destroy']);
    Route::get('paiements', [PaiementController::class, 'liste']);
    Route::get('paiements/{paiement}', [PaiementController::class, 'show']);
    Route::match(['put', 'patch'], 'paiements/{paiement}', [PaiementController::class, 'update']);
    Route::get('factures/{facture}/paiements', [PaiementController::class, 'index']);
    Route::post('factures/{facture}/paiements', [PaiementController::class, 'store']);
    Route::delete('paiements/{paiement}', [PaiementController::class, 'destroy']);
});

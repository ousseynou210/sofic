<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CommercialController;
use App\Http\Controllers\Admin\CompteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepenseController;
use App\Http\Controllers\Admin\FactureController;
use App\Http\Controllers\Admin\PaiementController;
use App\Http\Controllers\Admin\ParametreController;
use App\Http\Controllers\Admin\PointVenteController;
use App\Http\Controllers\Admin\ProduitController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::prefix('admin')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('admin.login.submit');
    });

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/parametres', [ParametreController::class, 'edit'])->name('admin.parametres.edit');
        Route::put('/parametres/email', [ParametreController::class, 'updateEmail'])->name('admin.parametres.email.update');
        Route::put('/parametres/mot-de-passe', [ParametreController::class, 'updatePassword'])->name('admin.parametres.password.update');

        Route::get('/client', [ClientController::class, 'index'])->name('admin.client.index');
        Route::get('/produit', [ProduitController::class, 'index'])->name('admin.produit.index');
        Route::get('/compte', [CompteController::class, 'index'])->name('admin.compte.index');
        Route::get('/commercial', [CommercialController::class, 'index'])->name('admin.commercial.index');
        Route::get('/point-vente', [PointVenteController::class, 'index'])->name('admin.point-vente.index');
        Route::get('/depense', [DepenseController::class, 'index'])->name('admin.depense.index');

        Route::resource('clients', ClientController::class)->only('index')->names('admin.clients');
        Route::resource('produits', ProduitController::class)->only('index')->names('admin.produits');
        Route::resource('comptes', CompteController::class)->only('index')->names('admin.comptes');
        Route::resource('commerciaux', CommercialController::class)->parameters(['commerciaux' => 'commercial'])->only('index')->names('admin.commerciaux');
        Route::resource('points-vente', PointVenteController::class)->only('index')->names('admin.points-vente');
        Route::resource('depenses', DepenseController::class)->only('index')->names('admin.depenses');
        Route::get('/factures', [FactureController::class, 'index'])->name('admin.factures.index');
        Route::get('/factures/{facture}', [FactureController::class, 'show'])
            ->whereNumber('facture')
            ->name('admin.factures.show');
    });

    Route::middleware(['auth', 'admin.web'])->group(function (): void {
        Route::resource('clients', ClientController::class)->except(['index', 'show'])->names('admin.clients');
        Route::resource('produits', ProduitController::class)->except(['index', 'show'])->names('admin.produits');
        Route::resource('comptes', CompteController::class)->except(['index', 'show'])->names('admin.comptes');
        Route::resource('commerciaux', CommercialController::class)->parameters(['commerciaux' => 'commercial'])->except(['index', 'show'])->names('admin.commerciaux');
        Route::resource('points-vente', PointVenteController::class)->except(['index', 'show'])->names('admin.points-vente');
        Route::resource('depenses', DepenseController::class)->except(['index', 'show'])->names('admin.depenses');

        Route::get('/factures/creer', [FactureController::class, 'create'])->name('admin.factures.create');
        Route::post('/factures', [FactureController::class, 'store'])->name('admin.factures.store');
        Route::get('/factures/{facture}/modifier', [FactureController::class, 'edit'])->whereNumber('facture')->name('admin.factures.edit');
        Route::put('/factures/{facture}', [FactureController::class, 'update'])->whereNumber('facture')->name('admin.factures.update');
        Route::post('/factures/{facture}/annuler', [FactureController::class, 'annuler'])->whereNumber('facture')->name('admin.factures.annuler');
        Route::delete('/factures/{facture}', [FactureController::class, 'destroy'])->whereNumber('facture')->name('admin.factures.destroy');
        Route::post('/factures/{facture}/paiements', [PaiementController::class, 'store'])->whereNumber('facture')->name('admin.factures.paiements.store');
        Route::delete('/paiements/{paiement}', [PaiementController::class, 'destroy'])->whereNumber('paiement')->name('admin.paiements.destroy');
    });
});

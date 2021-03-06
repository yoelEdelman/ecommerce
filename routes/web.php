<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('produits/{produit}', \App\Http\Controllers\ProductController::class)->name('produits.show');
Route::resource('panier', \App\Http\Controllers\CartController::class)->only(['index', 'store', 'update', 'destroy']);

// Utilisateur authentifié
Route::middleware('auth')->group(function () {
    // Commandes
    Route::prefix('commandes')->group(function () {
        Route::name('commandes.details')->post('details', \App\Http\Controllers\DetailsController::class);
        Route::name('commandes.confirmation')->get('confirmation/{order}', [\App\Http\Controllers\OrdersController::class, 'confirmation']);
        Route::name('commandes.payment')->post('paiement/{order}', \App\Http\Controllers\PaymentController::class);

        Route::resource('/', \App\Http\Controllers\OrderController::class)->names([
            'create' => 'commandes.create',
            'store' => 'commandes.store',
        ])->only(['create', 'store']);
    });
});

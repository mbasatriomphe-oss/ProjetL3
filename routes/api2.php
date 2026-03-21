<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\API\CategorieController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController; // Ajoutez cet import
use App\Http\Controllers\FournisseurController; // Ajoutez cet import
use App\Http\Controllers\ApprovisionnementController; // Ajoutez cet import

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes pour les unités (sans préfixe v1)
Route::prefix('unites')->group(function () {
    Route::get('/', [UniteController::class, 'getAll']);
    Route::get('/search', [UniteController::class, 'search']);
    Route::get('/{id}', [UniteController::class, 'getOne']);
    Route::post('/', [UniteController::class, 'create']);
    Route::put('/{id}', [UniteController::class, 'update']);
    Route::delete('/{id}', [UniteController::class, 'delete']);
});

// Routes pour les catégories (sans préfixe v1)
Route::get('/categories/search', [CategoryController::class, 'search']);
Route::get('/categories/recent', [CategoryController::class, 'recent']);
Route::get('/categories/count', [CategoryController::class, 'count']);
Route::get('/categories/search/name/{nom}', [CategoryController::class, 'searchByName']);
Route::get('/categories/search/paginated', [CategoryController::class, 'searchPaginated']);
Route::apiResource('categories', CategoryController::class);

// Routes pour les produits (recherche simple)
Route::get('/produits/search/{q}', [ProduitController::class, 'search']);
Route::get('/produits/categorie/{categorieId}', [ProduitController::class, 'byCategorie']);

/*
|--------------------------------------------------------------------------
| API Routes avec préfixe v1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    
    // ===== ROUTES PRODUITS =====
    Route::prefix('produits')->group(function () {
        Route::get('/', [ProduitController::class, 'getAll']);
        Route::get('/search', [ProduitController::class, 'search']);
        Route::get('/categorie/{categorieId}', [ProduitController::class, 'getByCategorie']);
        Route::get('/{id}', [ProduitController::class, 'getOne']);
        Route::post('/', [ProduitController::class, 'create']);
        Route::put('/{id}', [ProduitController::class, 'update']);
        Route::post('/{id}/photos', [ProduitController::class, 'ajouterPhotos']);
        Route::delete('/{id}', [ProduitController::class, 'delete']);
        Route::delete('/photos/{photoId}', [ProduitController::class, 'supprimerPhoto']);
    });
    
    // ===== ROUTES PUBLIQUES (AUTH) =====
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    
    // ===== ROUTES PROTÉGÉES (AUTH REQUIRED) =====
    Route::middleware('auth:sanctum')->group(function () {
        
        // Routes utilisateur
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        
        // Routes client
        Route::prefix('client')->group(function () {
            Route::post('/', [ClientController::class, 'create']);
            Route::get('/', [ClientController::class, 'getMyClient']);
            Route::put('/', [ClientController::class, 'update']);
        });
        
        
    });
});

Route::apiResource('fournisseurs', FournisseurController::class);
Route::prefix('approvisionnements')->group(function () {
    Route::get('/', [ApprovisionnementController::class, 'index']);
    Route::post('/', [ApprovisionnementController::class, 'store']);
    Route::get('/{id}', [ApprovisionnementController::class, 'show']);
    Route::put('/{id}', [ApprovisionnementController::class, 'update']);
    Route::delete('/{id}', [ApprovisionnementController::class, 'destroy']);
    
    // Routes pour les détails
    Route::post('/{id}/details', [ApprovisionnementController::class, 'addDetail']);
    Route::put('/{id}/details/{detailId}', [ApprovisionnementController::class, 'updateDetail']);
    Route::delete('/{id}/details/{detailId}', [ApprovisionnementController::class, 'deleteDetail']);
    
    // Route par fournisseur
    Route::get('/fournisseur/{fournisseurId}', [ApprovisionnementController::class, 'getByFournisseur']);
});
Route::apiResource('detail-approvisionnements', DetailApprovisionnementController::class);







// Routes pour les approvisionnements (admin uniquement)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1')->group(function () {
    
    // Approvisionnements
    Route::get('/approvisionnements', [ApprovisionnementController::class, 'index']);
    Route::get('/approvisionnements/statistics', [ApprovisionnementController::class, 'statistics']);
    Route::get('/approvisionnements/{id}', [ApprovisionnementController::class, 'getOne']);
    Route::post('/approvisionnements', [ApprovisionnementController::class, 'create']);
    Route::put('/approvisionnements/{id}', [ApprovisionnementController::class, 'update']);
    Route::delete('/approvisionnements/{id}', [ApprovisionnementController::class, 'delete']);
    
    // Gestion des produits dans un approvisionnement
    Route::post('/approvisionnements/{id}/produits', [ApprovisionnementController::class, 'ajouterProduit']);
    Route::delete('/approvisionnements/{id}/produits/{detailId}', [ApprovisionnementController::class, 'retirerProduit']);
    
    // Fournisseurs
    Route::apiResource('fournisseurs', FournisseurController::class);
});
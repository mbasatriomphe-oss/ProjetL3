<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ApprovisionnementController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes publiques pour les produits
Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{id}', [ProduitController::class, 'show']);

// ===== NOUVELLES ROUTES PUBLIQUES POUR PRODUITS =====
Route::get('/produits/search/paginate', [ProduitController::class, 'paginate']);
Route::get('/produits/search/latest', [ProduitController::class, 'latest']);
/*
|--------------------------------------------------------------------------
| API Routes avec préfixe v1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    
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
        
        // ===== ROUTES ADMIN SEULEMENT =====
        Route::middleware('admin')->group(function () {

            // Routes pour les unités
            Route::prefix('unites')->group(function () {
                Route::get('/', [UniteController::class, 'getAll']);
                Route::get('/search', [UniteController::class, 'search']);
                Route::get('/{id}', [UniteController::class, 'getOne']);
                Route::post('/', [UniteController::class, 'create']);
                Route::put('/{id}', [UniteController::class, 'update']);
                Route::delete('/{id}', [UniteController::class, 'delete']);
            });

            // Routes pour les catégories
            Route::get('/categories/search', [CategoryController::class, 'search']);
            Route::get('/categories/recent', [CategoryController::class, 'recent']);
            Route::get('/categories/count', [CategoryController::class, 'count']);
            Route::get('/categories/search/name/{nom}', [CategoryController::class, 'searchByName']);
            Route::get('/categories/search/paginated', [CategoryController::class, 'searchPaginated']);
            Route::apiResource('categories', CategoryController::class);

            // Routes pour les produits (admin seulement)
            // Routes pour les produits (admin seulement)
            Route::post('/produits', [ProduitController::class, 'store']);
            Route::put('/produits/{id}', [ProduitController::class, 'update']);
            Route::delete('/produits/{id}', [ProduitController::class, 'destroy']);
            Route::get('/produits/latest', [ProduitController::class, 'latest']);
            Route::get('/produits/paginate', [ProduitController::class, 'paginate']);

            // ===== ROUTES POUR LA GESTION DES PHOTOS =====
            Route::post('/produits/{id}/photos', [ProduitController::class, 'addPhotos']);           // Ajouter des photos
            Route::get('/produits/{id}/photos', [ProduitController::class, 'getPhotos']);            // Voir toutes les photos
            Route::delete('/produits/{produitId}/photos/{photoId}', [ProduitController::class, 'deletePhoto']); // Supprimer une photo
            Route::delete('/produits/{id}/photos', [ProduitController::class, 'deleteAllPhotos']);   // Supprimer toutes les photos
            Route::put('/produits/{id}/photos', [ProduitController::class, 'updatePhotos']);         // Remplacer toutes les photos
            Route::put('/produits/{produitId}/photos/{photoId}/main', [ProduitController::class, 'setMainPhoto']); // Définir photo principale']);

            // Routes pour les fournisseurs
        Route::prefix('fournisseurs')->group(function () {
            Route::get('/', [FournisseurController::class, 'index']);
            Route::get('/search', [FournisseurController::class, 'search']);
            Route::get('/latest', [FournisseurController::class, 'latest']);
            Route::get('/statistics', [FournisseurController::class, 'statistics']);
            Route::get('/{id}', [FournisseurController::class, 'show']);
            Route::post('/', [FournisseurController::class, 'store']);
            Route::put('/{id}', [FournisseurController::class, 'update']);
            Route::delete('/{id}', [FournisseurController::class, 'destroy']);
        });


        // Routes pour les approvisionnements
        Route::prefix('approvisionnements')->group(function () {
            Route::get('/', [ApprovisionnementController::class, 'index']);
            Route::get('/search', [ApprovisionnementController::class, 'search']);
            Route::get('/latest', [ApprovisionnementController::class, 'latest']);
            Route::get('/statistics', [ApprovisionnementController::class, 'statistics']);
            Route::get('/{id}', [ApprovisionnementController::class, 'show']);
            Route::post('/', [ApprovisionnementController::class, 'store']);
            Route::put('/{id}', [ApprovisionnementController::class, 'update']);
            Route::delete('/{id}', [ApprovisionnementController::class, 'destroy']);
        });
                                
        });
    });
});
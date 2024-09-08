<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('/register',[\App\Http\Controllers\Api\AuthController::class,'register']);

Route::middleware('auth:api')
    ->group(function (){
        Route::post('/logout',[\App\Http\Controllers\Api\AuthController::class,'logout'])->name('logout');

    });

    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
///route pour la déclaration 
    Route::post('/declarations', [DeclarationController::class, 'store']);
    Route::get('/produits/{id}', [DeclarationController::class, 'show']);
    Route::get('/vendeurs/{vendeur_id}/declarations', [DeclarationController::class, 'showByVendeur']);

    
    Route::middleware('auth:api')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('/categories', [CategorieController::class, 'index']);  // Liste toutes les catégories
            // Routes spécifiques aux administrateurs
            ///route qui permet de bloquer et de debloquer un utilisateur

            Route::put('/utilisateurs/{id}/bloquer', [AuthController::class, 'bloquerUtilisateur']);
            Route::put('/utilisateurs/{id}/debloquer', [AuthController::class, 'debloquerUtilisateur']);

            //Api pour les categories
          ///route pour les catégories
            Route::post('/categories', [CategorieController::class, 'store']); // Crée une nouvelle catégorie
            Route::get('/categories', [CategorieController::class, 'index']);  // Liste toutes les catégories
            Route::get('/categories/{id}', [CategorieController::class, 'show']); // Affiche une catégorie spécifique
            Route::put('/categories/{id}', [CategorieController::class, 'update']); // Met à jour une catégorie spécifique
            Route::delete('/categories/{id}', [CategorieController::class, 'destroy']); // Supprime une catégorie spécifique
            //route pour produits 
            Route::get('/produits', [ProduitController::class, 'index']); // Liste tous les produits
            Route::post('/produits', [ProduitController::class, 'store']); // Crée un nouveau produit
            Route::get('/produits/{id}', [ProduitController::class, 'show']); // Affiche un produit spécifique
            Route::put('/produits/{id}', [ProduitController::class, 'update']); // Met à jour un produit spécifique
            Route::delete('/produits/{id}', [ProduitController::class, 'destroy']); // Supprime un produit spécifique
            // Routes pour archiver et publier des produits
            Route::put('/produits/{id}/archiver', [ProduitController::class, 'archiver']);
            Route::put('/produits/{id}/publier', [ProduitController::class, 'publier']);
        });

        Route::middleware('role:vendeur')->group(function () {
            // Routes spécifiques aux vendeurs
            //Api produit
           Route::get('/litProduits', [ProduitController::class, 'indexV']); // Liste tous les produits
            ///route qui permet de modifier c'est informations
                Route::put('/modifier/{id}', [AuthController::class, 'update']);
            ///route pour la déclaration 
                Route::post('/declarations', [DeclarationController::class, 'store']);
                Route::get('/produits/{id}', [DeclarationController::class, 'show']);
                Route::get('/vendeurs/{vendeur_id}/declarations', [DeclarationController::class, 'showByVendeur']);
        });

        Route::middleware('role:client')->group(function () {
            // Routes spécifiques aux clients
        });
    });


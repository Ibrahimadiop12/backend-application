<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ReglementController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\LigneCommandeController;
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
            Route::get('/users', [AuthController::class, 'getUsers']);
            Route::patch('/user/{id}/status', [AuthController::class, 'updateStatus']);
            Route::patch('/userAdmin/{id}', [AuthController::class, 'update']);

            Route::put('/utilisateurs/{id}/bloquer', [AuthController::class, 'bloquerUtilisateur']);
            Route::put('/utilisateurs/{id}/debloquer', [AuthController::class, 'debloquerUtilisateur']);
            Route::delete('/user/{id}', [AuthController::class, 'destroy']); // Supprime une catégorie spécifique

            //Api pour les categories
          ///route pour les catégories
            Route::post('/categories', [CategorieController::class, 'store']); // Crée une nouvelle catégorie
            Route::patch('/categories/{id}/status', [CategorieController::class, 'updateStatus']);
            Route::get('/categories', [CategorieController::class, 'index']);  // Liste toutes les catégories
            Route::get('/categories/{id}', [CategorieController::class, 'show']); // Affiche une catégorie spécifique
            Route::put('/categories/{id}', [CategorieController::class, 'update']); // Met à jour une catégorie spécifique
            Route::delete('/categories/{id}', [CategorieController::class, 'destroy']); // Supprime une catégorie spécifique
            //route pour produits
            Route::get('/produits', [ProduitController::class, 'index']); // Liste tous les produits
            Route::post('/produits', [ProduitController::class, 'store']); // Crée un nouveau produit
            Route::put('/produits/{id}', [ProduitController::class, 'update']); // Met à jour un produit spécifique
            Route::delete('/produits/{id}', [ProduitController::class, 'destroy']); // Supprime un produit spécifique
            // Routes pour archiver et publier des produits
            Route::put('/produits/{id}/archiver', [ProduitController::class, 'archiver']);
            Route::put('/produits/{id}/publier', [ProduitController::class, 'publier']);
              // Route pour mettre à jour le statut d'une catégorie
              Route::patch('/produits/{id}/status', [ProduitController::class, 'updateStatus']);
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
                // routes/api.php
                Route::put('/vendeurs/{vendeur_id}/declarations/{id}/statut', [DeclarationController::class, 'updateStatut']);

            // Route pour récupérer la liste des produits
            Route::get('/produitsid/{id}', [ProduitController::class, 'show']); // Affiche un produit spécifique



        });

        Route::middleware('role:client')->group(function () {
            // Routes spécifiques aux clients
            Route::post('/lignes-commandes', [LigneCommandeController::class, 'store']);
            Route::get('/lignes-commandes/user', [LigneCommandeController::class, 'getLignesCommandes']);
            ///Commande
            Route::post('commandes', [CommandeController::class, 'store']);
            Route::get('commandes', [CommandeController::class, 'index']);
            Route::get('/commandes/{id}', [CommandeController::class, 'show']);
            ///Methode incrément et décrément de la quantité
            Route::post('/lignes-commandes/incrementer', [CommandeController::class, 'incrementerQuantite']);
            Route::post('/lignes-commandes/decrementer', [CommandeController::class, 'decrementerQuantite']);
            ///Méthode qui permet de supprimer
            Route::delete('/lignes-commandes/supprimer', [CommandeController::class, 'supprimerLigneCommande']);
            ///Api pour vider le panier

            ///methode qui compte le nombre de ligne de commande
            Route::get('/lignes-par-utilisateur', [LigneCommandeController::class, 'compterLignesParUtilisateur']);
             Route::post('/store-payment', [ReglementController::class, 'storePayment']);
            // web.php ou api.php



        });
    });
    Route::get('/declarations', [DeclarationController::class, 'index']);
    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::post('/payment-cash', [PaymentController::class, 'paymentByCash']);
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
    // routes/api.php


    Route::middleware('auth:api')->get('/user', [AuthController::class, 'getUserInfo']);

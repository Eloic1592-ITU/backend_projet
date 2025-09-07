<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TouristAttractionController;
use App\Http\Controllers\TouristCircuitContoller;


// Authentifications
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Utilisateurs
Route::prefix('users')
    ->controller(UserController::class)->group(function () {
        Route::get('/', action: 'getUser'); // Tous les utilisateurs
        Route::get('/{id}', 'getUserById');  // Détail d’un site
    });

// sites touristiques
Route::prefix('sites')
    ->controller(TouristAttractionController::class)->group(function () {
        Route::get('/', 'index');                      // Tous les sites
        Route::get('/{id}', 'showInfo');               // Détail d’un site
        Route::post('/', 'store');                     // Créer un site
        Route::put('/{id}', 'update');                 // Modifier un site
        Route::delete('/{id}', 'destroy');             // Supprimer un site
        Route::put('/{id}/status-publish', 'modifyPublicationStatus'); // Modifier le statut de publication
    });

// Circuit touristiques
Route::prefix('circuits')
    ->controller(TouristCircuitContoller::class)->group(function () {
        Route::get('/', 'index');                      // Tous les sites
        Route::get('/{id}', 'showInfo');              // Détail d’un site
        Route::post('/', 'store');                     // Créer un site
        Route::put('/{id}', 'update');                 // Modifier un site
        Route::delete('/{id}', 'destroy');             // Supprimer un site
        Route::put('/{id}/status-publish', 'modifyPublicationStatus'); // Modifier le statut de publication
    });



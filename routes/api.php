<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TouristAttractionController;


// Authentifications
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Utilisateurs
Route::prefix('users')->controller(UserController::class)->group(function () {
    Route::get('/', 'getUser'); // Tous les utilisateurs
    Route::get('/{id}', 'getUserById');  // Détail d’un site
});

// sites touristiques
Route::prefix('sites')->controller(TouristAttractionController::class)->group(function () {
    Route::get('/', 'index');                      // Tous les sites
    Route::get('/{id}', 'showInfo');               // Détail d’un site
    Route::post('/', 'store');                     // Créer un site
    Route::put('/{id}', 'update');                 // Modifier un site
    Route::delete('/{id}', 'destroy');             // Supprimer un site
    Route::put('/{id}/status-publish', 'modifyPublicationStatus'); // Modifier le statut de publication
});

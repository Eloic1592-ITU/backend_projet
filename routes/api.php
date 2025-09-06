<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TouristAttractionController;


// Utilisateurs
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [AuthController::class, 'getUser']);



// CRUD sites touristiques
Route::get('/sites', [TouristAttractionController::class, 'index']);      // Tous les sites
Route::get('/sites/{id}', [TouristAttractionController::class, 'show']); // Détail d’un site
Route::post('/sites', [TouristAttractionController::class, 'store']);    // Créer un site
Route::put('/sites/{id}', [TouristAttractionController::class, 'update']); // Modifier un site
Route::delete('/sites/{id}', [TouristAttractionController::class, 'destroy']); // Supprimer un site

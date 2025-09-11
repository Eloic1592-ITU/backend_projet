<?php

use App\Http\Controllers\AccomodationController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\HebergementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TouristAttractionController;
use App\Http\Controllers\TouristCircuitContoller;
use App\Http\Controllers\ReservationController;


// Authentifications
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/check', [AuthController::class, 'checkConnectionState']);


// Utilisateurs
Route::prefix('users')
    ->middleware('verifyToken')
    ->controller(UserController::class)->group(function () {
        Route::get('/', action: 'getUser'); // Tous les utilisateurs
        Route::get('/{id}', 'getUserById');  // Détail d’un site
    });

// sites touristiques
Route::prefix('sites')
    // ->middleware('verifyToken')
    ->controller(TouristAttractionController::class)->group(function () {
        Route::get('/', 'index');                      // Tous les sites
        Route::get('/{id}', 'showInfo');               // Détail d’un site
        Route::post('/', 'store');                     // Créer un site
        Route::post('/{id}', 'update');                 // Modifier un site
        Route::delete('/{id}', 'destroy');             // Supprimer un site
        Route::put('/{id}/status-publish', 'modifyPublicationStatus'); // Modifier le statut de publication
    });

// Circuit touristiques
Route::prefix('circuits')
    // ->middleware('verifyToken')
    ->controller(TouristCircuitContoller::class)->group(function () {
        Route::get('/', 'index');                      // Tous les sites
        Route::get('/{id}', 'showInfo');              // Détail d’un site
        Route::post('/', 'store');                     // Créer un site
        Route::post('/{id}', 'update');                 // Modifier un site
        Route::delete('/{id}', 'destroy');             // Supprimer un site
        Route::put('/{id}/status-publish', 'modifyPublicationStatus'); // Modifier le statut de publication
    });



// Reservation
Route::prefix('reservations')
    // ->middleware('verifyToken')
    ->controller(ReservationController::class)->group(function () {
        Route::get('/', 'index');                      // Tous les reservations
        Route::get('/{id}', 'showInfo');              // Détail d’un reservation
        Route::post('/', 'store');                     // Créer un reservation
        Route::put('/{id}', 'update');                 // Modifier un reservation
        Route::delete('/{id}', 'destroy');             // Supprimer un reservation
        Route::put('/{id}/status-payment', 'modifyReservationStatus'); // Modifier le statut de reservations
    });

// Commentaire
Route::prefix('commentaires')
    // ->middleware('verifyToken')
    ->controller(CommentaireController::class)->group(function () {
        Route::get('/', 'index');                      // Tous les commentaire
        Route::get('/{id}', 'showInfo');              // Détail d’un commentaire
        Route::post('/', 'store');                     // Créer un commentaire
        Route::put('/{id}', 'update');                 // Modifier un commentaire
        // Route::put('/{id}/status-comment', 'updateStatut'); // Modifier le statut de publication
        Route::get('/site/{id_site}', 'findByIdSite');
        Route::get('/circuit/{id_circuit}', 'findByIdCircuit');

    });


// Commodites
Route::prefix('commodites')
    // ->middleware('verifyToken')
    ->controller(CommodityController::class)->group(function () {
        Route::get('/', 'index');
    });

Route::prefix('accomodations')
    // ->middleware('verifyToken')
    ->controller(HebergementController::class)->group(function () {
        Route::get('/', 'index');
    });

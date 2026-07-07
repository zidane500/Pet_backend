<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\LostFoundController;
use App\Http\Controllers\Api\VetController;
use App\Http\Controllers\Api\PetStoreController;
use App\Http\Controllers\Api\ShelterController;
use App\Http\Controllers\Api\BreederController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\AdminController;

// ─── Auth publique ────────────────────────────────────────────
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/register',         [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login',            [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/forgot-password',  [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password',   [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');
});

// ─── Publiques (lecture seule) ────────────────────────────────
Route::get('/listings',              [ListingController::class, 'index']);
Route::get('/listings/{id}',         [ListingController::class, 'show']);
Route::get('/lost-found',            [LostFoundController::class, 'index']);

// Vétérinaires
Route::get('/vets',                  [VetController::class, 'index']);
Route::get('/vets/{id}',             [VetController::class, 'show']);

// Animaleries
Route::get('/pet-stores',            [PetStoreController::class, 'index']);
Route::get('/pet-stores/{id}',       [PetStoreController::class, 'show']);

// Refuges
Route::get('/shelters',              [ShelterController::class, 'index']);
Route::get('/shelters/{id}',         [ShelterController::class, 'show']);

// Éleveurs
Route::get('/breeders',              [BreederController::class, 'index']);
Route::get('/breeders/{id}',         [BreederController::class, 'show']);

// Profils publics
Route::get('/users/{id}',            [UserController::class, 'show']);

// ─── Protégées (token requis) ─────────────────────────────────
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // Auth
    Route::post('/auth/logout',      [AuthController::class, 'logout']);
    Route::get('/auth/me',           [AuthController::class, 'me']);

    // Profil
    Route::put('/profile',           [UserController::class, 'update']);
    Route::get('/dashboard',         [UserController::class, 'dashboard']);

    // Annonces
    Route::post('/listings',         [ListingController::class, 'store']);
    Route::put('/listings/{id}',     [ListingController::class, 'update']);
    Route::delete('/listings/{id}',  [ListingController::class, 'destroy']);
    Route::get('/my-listings',       [ListingController::class, 'myListings']);

    // Lost & Found
    Route::post('/lost-found',              [LostFoundController::class, 'store']);
    Route::put('/lost-found/{id}',          [LostFoundController::class, 'update']);
    Route::delete('/lost-found/{id}',       [LostFoundController::class, 'destroy']);
    Route::patch('/lost-found/{id}/resolve',[LostFoundController::class, 'resolve']);

    // Refuges (gestion par le propriétaire)
    Route::post('/shelters',         [ShelterController::class, 'store']);
    Route::put('/shelters/{id}',     [ShelterController::class, 'update']);
    Route::delete('/shelters/{id}',  [ShelterController::class, 'destroy']);

    // Éleveurs (gestion par le propriétaire)
    Route::post('/breeders',         [BreederController::class, 'store']);
    Route::put('/breeders/{id}',     [BreederController::class, 'update']);
    Route::delete('/breeders/{id}',  [BreederController::class, 'destroy']);

    // Messages
    Route::get('/conversations',              [MessageController::class, 'conversations'])->middleware('throttle:120,1');
    Route::get('/conversations/{userId}',     [MessageController::class, 'show'])->whereNumber('userId')->middleware('throttle:120,1');
    Route::post('/messages',                  [MessageController::class, 'store'])->middleware('throttle:20,1');
    Route::patch('/messages/{id}/read',       [MessageController::class, 'markRead'])->whereNumber('id')->middleware('throttle:60,1');

    // Favoris
    Route::get('/favorites',         [FavoriteController::class, 'index'])->middleware('throttle:120,1');
    Route::post('/favorites',        [FavoriteController::class, 'toggle'])->middleware('throttle:60,1');

    // Avis
    Route::post('/reviews',          [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}',   [ReviewController::class, 'destroy']);

    // Notifications
    Route::get('/notifications',                   [UserController::class, 'notifications'])->middleware('throttle:120,1');
    Route::patch('/notifications/{id}/read',       [UserController::class, 'markNotificationRead'])->middleware('throttle:60,1');
    Route::patch('/notifications/read-all',        [UserController::class, 'markAllNotificationsRead'])->middleware('throttle:30,1');
    Route::delete('/notifications',                [UserController::class, 'deleteNotifications'])->middleware('throttle:10,1');

    // Upload photos
    Route::post('/upload',           [UploadController::class, 'store'])->middleware('throttle:20,1');
});

// ─── Routes Admin (auth + role admin requis) ──────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Statistiques globales
    Route::get('/stats',                          [AdminController::class, 'stats']);

    // CRUD Utilisateurs
    Route::get('/users',                          [AdminController::class, 'users']);
    Route::get('/users/{id}',                     [AdminController::class, 'showUser']);
    Route::post('/users',                         [AdminController::class, 'createUser']);
    Route::put('/users/{id}',                     [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}',                  [AdminController::class, 'deleteUser']);
    Route::post('/users/{id}/ban',                [AdminController::class, 'banUser']);
    Route::post('/users/{id}/unban',              [AdminController::class, 'unbanUser']);
    Route::post('/users/{id}/verify',             [AdminController::class, 'verifyUser']);

    // CRUD Annonces
    Route::get('/listings',                       [AdminController::class, 'listings']);
    Route::delete('/listings/{id}',               [AdminController::class, 'deleteListing']);
    Route::patch('/listings/{id}/toggle',         [AdminController::class, 'toggleListingActive']);
});
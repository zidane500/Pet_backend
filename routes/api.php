<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\LostFoundController;
use App\Http\Controllers\Api\VetController;
use App\Http\Controllers\Api\PetStoreController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UploadController;

// ─── Auth publique ────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// ─── Publiques (lecture) ──────────────────────────────────────
Route::get('/listings',          [ListingController::class, 'index']);
Route::get('/listings/{id}',     [ListingController::class, 'show']);
Route::get('/lost-found',        [LostFoundController::class, 'index']);
Route::get('/vets',              [VetController::class, 'index']);
Route::get('/vets/{id}',         [VetController::class, 'show']);
Route::get('/pet-stores',        [PetStoreController::class, 'index']);
Route::get('/pet-stores/{id}',   [PetStoreController::class, 'show']);
Route::get('/users/{id}',        [UserController::class, 'show']);

// ─── Protégées (token requis) ─────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Profil
    Route::put('/profile',        [UserController::class, 'update']);
    Route::get('/dashboard',      [UserController::class, 'dashboard']);

    // Annonces
    Route::post('/listings',           [ListingController::class, 'store']);
    Route::put('/listings/{id}',       [ListingController::class, 'update']);
    Route::delete('/listings/{id}',    [ListingController::class, 'destroy']);
    Route::get('/my-listings',         [ListingController::class, 'myListings']);

    // Lost & Found
    Route::post('/lost-found',         [LostFoundController::class, 'store']);
    Route::put('/lost-found/{id}',     [LostFoundController::class, 'update']);
    Route::delete('/lost-found/{id}',  [LostFoundController::class, 'destroy']);
    Route::patch('/lost-found/{id}/resolve', [LostFoundController::class, 'resolve']);

    // Messages
    Route::get('/conversations',              [MessageController::class, 'conversations']);
    Route::get('/conversations/{userId}',     [MessageController::class, 'show']);
    Route::post('/messages',                  [MessageController::class, 'store']);
    Route::patch('/messages/{id}/read',       [MessageController::class, 'markRead']);

    // Favoris
    Route::get('/favorites',           [FavoriteController::class, 'index']);
    Route::post('/favorites',          [FavoriteController::class, 'toggle']);

    // Avis
    Route::post('/reviews',            [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}',     [ReviewController::class, 'destroy']);

    // Notifications
    Route::get('/notifications',              [UserController::class, 'notifications']);
    Route::patch('/notifications/{id}/read',  [UserController::class, 'markNotificationRead']);
    Route::patch('/notifications/read-all',   [UserController::class, 'markAllNotificationsRead']);

    // Upload photos
    Route::post('/upload',             [UploadController::class, 'store']);
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SpaceController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\Admin\DashboardController;
use App\Http\Controllers\API\Admin\SpaceController as AdminSpaceController;
use App\Http\Controllers\API\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\API\Admin\GalleryController as AdminGalleryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =============================================
// ROUTES PUBLIQUES
// =============================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/spaces', [SpaceController::class, 'index']);
Route::get('/spaces/{slug}', [SpaceController::class, 'show']);
Route::get('/spaces/{id}/availability', [SpaceController::class, 'checkAvailability']);
Route::get('/spaces/{id}/pricing', [BookingController::class, 'getPricingOptions']);

Route::get('/gallery', [GalleryController::class, 'index']);
Route::get('/gallery/{slug}', [GalleryController::class, 'show']);

// =============================================
// ROUTES RÉSERVATIONS (publiques)
// =============================================
Route::prefix('bookings')->group(function () {
    Route::post('/', [BookingController::class, 'store']);
    Route::get('/{token}/confirm', [BookingController::class, 'confirm']);
    Route::get('/{token}/status', [BookingController::class, 'status']);
    Route::put('/{token}/cancel', [BookingController::class, 'cancelByToken']);
});

// =============================================
// ROUTES UTILISATEUR (authentifié)
// =============================================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    
    Route::prefix('my-bookings')->group(function () {
        Route::get('/', [BookingController::class, 'myBookings']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::put('/{id}/cancel', [BookingController::class, 'cancel']);
    });
});

// =============================================
// ROUTES ADMIN
// =============================================
Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {
        
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        
        // Espaces - apiResource sans les routes en conflit
        Route::apiResource('spaces', AdminSpaceController::class)->except(['show']);
        Route::get('/spaces/{id}', [AdminSpaceController::class, 'show']);
        
        // Images multiples
        Route::prefix('spaces/{id}')->group(function () {
            Route::get('/images', [AdminSpaceController::class, 'getImages']);
            Route::post('/images', [AdminSpaceController::class, 'addImage']);
            Route::put('/images/{imageId}', [AdminSpaceController::class, 'updateImage']);
            Route::delete('/images/{imageId}', [AdminSpaceController::class, 'deleteImage']);
        });
        
        // Options de prix
        Route::prefix('spaces/{id}/pricing')->group(function () {
            Route::post('/', [AdminSpaceController::class, 'addPricingOption']);
            Route::put('/{pricingId}', [AdminSpaceController::class, 'updatePricingOption']);
            Route::delete('/{pricingId}', [AdminSpaceController::class, 'deletePricingOption']);
        });
        
        // Réservations
        Route::prefix('bookings')->group(function () {
            Route::get('/', [AdminBookingController::class, 'index']);
            Route::get('/stats', [AdminBookingController::class, 'stats']);
            Route::get('/export', [AdminBookingController::class, 'export']);
            Route::put('/{id}', [AdminBookingController::class, 'update']);
            Route::delete('/{id}', [AdminBookingController::class, 'destroy']);
        });
        
        // Galerie
        Route::apiResource('gallery', AdminGalleryController::class);
    });
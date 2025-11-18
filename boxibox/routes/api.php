<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\ReservationController as ApiReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Mobile App API Routes for Boxibox Customers
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/login', [AuthController::class, 'login']);

    // Promotions publiques
    Route::get('/promotions', [PromotionController::class, 'index']);

    // Recherche de boxes
    Route::post('/boxes/search', [ApiReservationController::class, 'search']);
    Route::post('/boxes/calculate-price', [ApiReservationController::class, 'calculatePrice']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware(['auth:sanctum', \App\Http\Middleware\EnsureTenantFromCustomer::class])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::get('/profile/statistics', [ProfileController::class, 'statistics']);

    // Contracts
    Route::get('/contracts', [ContractController::class, 'index']);
    Route::get('/contracts/{id}', [ContractController::class, 'show']);
    Route::post('/contracts/{id}/request-termination', [ContractController::class, 'requestTermination']);
    Route::get('/contracts/termination-requests', [ContractController::class, 'terminationRequests']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);

    // Issues (Signalements)
    Route::get('/issues', [IssueController::class, 'index']);
    Route::get('/issues/{id}', [IssueController::class, 'show']);
    Route::post('/issues', [IssueController::class, 'store']);

    // Notifications
    Route::post('/notifications/register-token', [NotificationController::class, 'registerToken']);
    Route::post('/notifications/unregister-token', [NotificationController::class, 'unregisterToken']);
    Route::get('/notifications/tokens', [NotificationController::class, 'getTokens']);
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);

    // Reservations
    Route::get('/reservations', [ApiReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ApiReservationController::class, 'show']);
    Route::post('/reservations', [ApiReservationController::class, 'store']);
    Route::post('/reservations/{id}/cancel', [ApiReservationController::class, 'cancel']);

    // Promotions
    Route::post('/promotions/validate', [PromotionController::class, 'validate']);

    // Loyalty Points
    Route::get('/loyalty/balance', [LoyaltyController::class, 'balance']);
    Route::get('/loyalty/history', [LoyaltyController::class, 'history']);
    Route::get('/loyalty/info', [LoyaltyController::class, 'info']);
});

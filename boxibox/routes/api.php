<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
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
});

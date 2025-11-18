<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Plan view
    Route::get('/plan', [PlanController::class, 'index'])->name('plan.index');

    // Sites management
    Route::resource('sites', SiteController::class);

    // Boxes management
    Route::resource('boxes', BoxController::class);
    Route::get('/sites/{site}/boxes', [BoxController::class, 'bySite'])->name('boxes.bySite');

    // Customers management
    Route::resource('customers', CustomerController::class);

    // Contracts management
    Route::resource('contracts', ContractController::class);
    Route::post('/contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');
    Route::post('/contracts/{contract}/suspend', [ContractController::class, 'suspend'])->name('contracts.suspend');
    Route::post('/contracts/{contract}/reactivate', [ContractController::class, 'reactivate'])->name('contracts.reactivate');

    // Invoices management
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
});

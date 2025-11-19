<?php

use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\ContractController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use Illuminate\Support\Facades\Route;

// Customer Portal Routes
Route::middleware(['auth:sanctum'])->prefix('my')->name('customer.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contracts
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::post('/contracts/{contract}/terminate', [ContractController::class, 'requestTermination'])->name('contracts.terminate');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/invoices/{invoice}/pay', [PaymentController::class, 'pay'])->name('invoices.pay');
    Route::get('/payments/paypal/success', [PaymentController::class, 'paypalSuccess'])->name('payments.paypal.success');
    Route::get('/payments/paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('payments.paypal.cancel');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

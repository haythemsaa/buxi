<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\WebhookController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Réservations (publiques)
Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
Route::post('/reservations/search', [ReservationController::class, 'search'])->name('reservations.search');
Route::post('/reservations/calculate-price', [ReservationController::class, 'calculatePrice'])->name('reservations.calculate-price');
Route::post('/reservations/compare', [ReservationController::class, 'compare'])->name('reservations.compare');
Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Customer Portal Routes
require __DIR__.'/customer.php';

// Admin Revenue Management Routes
require __DIR__.'/admin_revenue.php';

// Webhook Routes (CSRF excluded in bootstrap/app.php)
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');

require __DIR__.'/auth.php';

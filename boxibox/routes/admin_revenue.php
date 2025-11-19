<?php

use App\Http\Controllers\Admin\PricingRuleController;
use App\Http\Controllers\Admin\RevenueManagementController;
use Illuminate\Support\Facades\Route;

// Revenue Management & Dynamic Pricing
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Revenue Management Dashboard
    Route::get('/revenue-management', [RevenueManagementController::class, 'index'])
        ->name('revenue-management.index');
    Route::post('/revenue-management/sites/{site}/update-prices', [RevenueManagementController::class, 'updatePrices'])
        ->name('revenue-management.update-prices');
    Route::post('/revenue-management/sites/{site}/simulate', [RevenueManagementController::class, 'simulate'])
        ->name('revenue-management.simulate');
    Route::get('/revenue-management/sites/{site}/analytics', [RevenueManagementController::class, 'analytics'])
        ->name('revenue-management.analytics');

    // Pricing Rules CRUD
    Route::resource('pricing-rules', PricingRuleController::class);
    Route::post('/pricing-rules/{pricingRule}/toggle', [PricingRuleController::class, 'toggle'])
        ->name('pricing-rules.toggle');
});

<?php

use App\Http\Controllers\Admin\SiteManagementController;
use App\Http\Controllers\Admin\BuildingManagementController;
use App\Http\Controllers\Admin\FloorManagementController;
use App\Http\Controllers\Admin\BoxManagementController;
use App\Http\Controllers\Admin\FloorPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Infrastructure Routes
|--------------------------------------------------------------------------
|
| Routes for managing Sites, Buildings, Floors, Boxes and Floor Plans
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // =========================================================================
    // FLOOR PLAN EDITOR
    // =========================================================================
    Route::prefix('floor-plan')->name('floor-plan.')->group(function () {
        Route::get('/', [FloorPlanController::class, 'index'])->name('index');
        Route::get('/floors/{floor}', [FloorPlanController::class, 'edit'])->name('edit');
        Route::post('/floors/{floor}/update-positions', [FloorPlanController::class, 'updateBoxPositions'])->name('update-positions');
        Route::post('/floors/{floor}/settings', [FloorPlanController::class, 'updateFloorSettings'])->name('update-settings');
        Route::post('/floors/{floor}/auto-organize', [FloorPlanController::class, 'autoOrganize'])->name('auto-organize');
        Route::get('/floors/{floor}/data', [FloorPlanController::class, 'getFloorData'])->name('data');
    });

    // =========================================================================
    // SITES MANAGEMENT
    // =========================================================================
    Route::resource('sites', SiteManagementController::class);

    // =========================================================================
    // BUILDINGS MANAGEMENT (nested under sites)
    // =========================================================================
    Route::prefix('sites/{site}/buildings')->name('sites.buildings.')->group(function () {
        Route::get('/', [BuildingManagementController::class, 'index'])->name('index');
        Route::get('/create', [BuildingManagementController::class, 'create'])->name('create');
        Route::post('/', [BuildingManagementController::class, 'store'])->name('store');
        Route::get('/{building}', [BuildingManagementController::class, 'show'])->name('show');
        Route::get('/{building}/edit', [BuildingManagementController::class, 'edit'])->name('edit');
        Route::put('/{building}', [BuildingManagementController::class, 'update'])->name('update');
        Route::delete('/{building}', [BuildingManagementController::class, 'destroy'])->name('destroy');
    });

    // =========================================================================
    // FLOORS MANAGEMENT (nested under buildings)
    // =========================================================================
    Route::prefix('buildings/{building}/floors')->name('buildings.floors.')->group(function () {
        Route::get('/', [FloorManagementController::class, 'index'])->name('index');
        Route::get('/create', [FloorManagementController::class, 'create'])->name('create');
        Route::post('/', [FloorManagementController::class, 'store'])->name('store');
        Route::get('/{floor}', [FloorManagementController::class, 'show'])->name('show');
        Route::get('/{floor}/edit', [FloorManagementController::class, 'edit'])->name('edit');
        Route::put('/{floor}', [FloorManagementController::class, 'update'])->name('update');
        Route::delete('/{floor}', [FloorManagementController::class, 'destroy'])->name('destroy');
    });

    // =========================================================================
    // BOXES MANAGEMENT (nested under floors)
    // =========================================================================
    Route::prefix('floors/{floor}/boxes')->name('floors.boxes.')->group(function () {
        Route::get('/', [BoxManagementController::class, 'index'])->name('index');
        Route::get('/create', [BoxManagementController::class, 'create'])->name('create');
        Route::post('/', [BoxManagementController::class, 'store'])->name('store');
        Route::post('/bulk', [BoxManagementController::class, 'storeBulk'])->name('store-bulk');
        Route::get('/{box}', [BoxManagementController::class, 'show'])->name('show');
        Route::get('/{box}/edit', [BoxManagementController::class, 'edit'])->name('edit');
        Route::put('/{box}', [BoxManagementController::class, 'update'])->name('update');
        Route::delete('/{box}', [BoxManagementController::class, 'destroy'])->name('destroy');
        Route::patch('/{box}/color', [BoxManagementController::class, 'updateColor'])->name('update-color');
    });
});

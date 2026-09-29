<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboards', [DashboardController::class, 'index'])->name('dashboards.index');
    Route::get('/dashboards/create', [DashboardController::class, 'create'])->name('dashboards.create');
    Route::post('/dashboards', [DashboardController::class, 'store'])->name('dashboards.store');
    Route::post('/dashboards/filters', [DashboardController::class, 'storeFilters'])->name('dashboards.filters.store');
    Route::get('/dashboards/{dashboard}', [DashboardController::class, 'show'])->name('dashboards.show');
    Route::get('/dashboards/{dashboard}/edit', [DashboardController::class, 'edit'])->name('dashboards.edit');
    Route::put('/dashboards/{dashboard}', [DashboardController::class, 'update'])->name('dashboards.update');
    Route::delete('/dashboards/{dashboard}', [DashboardController::class, 'destroy'])->name('dashboards.destroy');
    Route::post('/dashboards/{dashboard}/clone', [DashboardController::class, 'clone'])->name('dashboards.clone');
});

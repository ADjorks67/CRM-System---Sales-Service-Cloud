<?php

use App\Http\Controllers\OpportunityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/create', [OpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');
    Route::post('/opportunities/bulk', [OpportunityController::class, 'bulk'])->name('opportunities.bulk');
    Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show'])->name('opportunities.show');
    Route::get('/opportunities/{opportunity}/edit', [OpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::put('/opportunities/{opportunity}', [OpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{opportunity}', [OpportunityController::class, 'destroy'])->name('opportunities.destroy');
    Route::post('/opportunities/{opportunity}/archive', [OpportunityController::class, 'archive'])->name('opportunities.archive');
    Route::post('/opportunities/{opportunity}/change-owner', [OpportunityController::class, 'changeOwner'])->name('opportunities.change-owner');
    Route::post('/opportunities/{opportunity}/change-stage', [OpportunityController::class, 'updateStage'])->name('opportunities.change-stage');
    Route::get('/opportunities/{opportunity}/clone', [OpportunityController::class, 'cloneForm'])->name('opportunities.clone');
    Route::post('/opportunities/{opportunity}/clone', [OpportunityController::class, 'clone'])->name('opportunities.clone.store');
});

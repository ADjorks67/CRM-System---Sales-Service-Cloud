<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/leads/bulk', [LeadController::class, 'bulk'])->name('leads.bulk');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{lead}/change-owner', [LeadController::class, 'changeOwner'])->name('leads.change-owner');
    Route::post('/leads/{lead}/change-status', [LeadController::class, 'changeStatus'])->name('leads.change-status');
    Route::get('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'storeConversion'])->name('leads.convert.store');
});

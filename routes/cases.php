<?php

use App\Http\Controllers\CrmCaseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/cases', [CrmCaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/create', [CrmCaseController::class, 'create'])->name('cases.create');
    Route::post('/cases', [CrmCaseController::class, 'store'])->name('cases.store');
    Route::post('/cases/bulk', [CrmCaseController::class, 'bulk'])->name('cases.bulk');
    Route::get('/cases/{crmCase}', [CrmCaseController::class, 'show'])->name('cases.show');
    Route::get('/cases/{crmCase}/edit', [CrmCaseController::class, 'edit'])->name('cases.edit');
    Route::put('/cases/{crmCase}', [CrmCaseController::class, 'update'])->name('cases.update');
    Route::delete('/cases/{crmCase}', [CrmCaseController::class, 'destroy'])->name('cases.destroy');
    Route::post('/cases/{crmCase}/change-owner', [CrmCaseController::class, 'changeOwner'])->name('cases.change-owner');
    Route::post('/cases/{crmCase}/change-status', [CrmCaseController::class, 'changeStatus'])->name('cases.change-status');
    Route::post('/cases/{crmCase}/reopen', [CrmCaseController::class, 'reopen'])->name('cases.reopen');
});

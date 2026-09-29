<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::post('/accounts/bulk', [AccountController::class, 'bulk'])->name('accounts.bulk');
    Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::post('/accounts/{account}/change-owner', [AccountController::class, 'changeOwner'])->name('accounts.change-owner');
});

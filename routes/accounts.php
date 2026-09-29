<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
});

<?php

use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{api_token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});

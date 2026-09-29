<?php

use App\Http\Controllers\Auth\MfaChallengeController;
use App\Http\Controllers\MfaSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/mfa/challenge', [MfaChallengeController::class, 'create'])->name('mfa.challenge');
    Route::post('/mfa/challenge', [MfaChallengeController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('mfa.challenge.store');
    Route::post('/mfa/challenge/resend', [MfaChallengeController::class, 'resend'])
        ->middleware('throttle:5,1')
        ->name('mfa.challenge.resend');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/mfa', [MfaSettingsController::class, 'edit'])->name('mfa.edit');
    Route::post('/mfa/enable', [MfaSettingsController::class, 'enable'])->name('mfa.enable');
    Route::post('/mfa/disable', [MfaSettingsController::class, 'disable'])->name('mfa.disable');
    Route::post('/mfa/regenerate', [MfaSettingsController::class, 'regenerate'])->name('mfa.regenerate');
    Route::post('/users/{user}/mfa/disable', [MfaSettingsController::class, 'adminDisable'])->name('users.mfa.disable');
});

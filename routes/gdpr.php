<?php

use App\Http\Controllers\GdprController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('gdpr')->name('gdpr.')->group(function (): void {
    Route::get('/', [GdprController::class, 'index'])->name('index');
    Route::post('/export', [GdprController::class, 'export'])->name('export');
    Route::post('/anonymize', [GdprController::class, 'anonymize'])->name('anonymize');
});

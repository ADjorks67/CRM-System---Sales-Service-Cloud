<?php

use App\Http\Controllers\DataExportController;
use App\Http\Controllers\DataImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/imports', [DataImportController::class, 'index'])->name('imports.index');
    Route::post('/imports/preview', [DataImportController::class, 'preview'])->name('imports.preview');
    Route::post('/imports', [DataImportController::class, 'store'])->name('imports.store');
    Route::get('/imports/errors.csv', [DataImportController::class, 'downloadErrors'])->name('imports.errors');

    Route::get('/exports', [DataExportController::class, 'index'])->name('exports.index');
    Route::get('/exports/{object}.csv', [DataExportController::class, 'download'])->name('exports.download');
});

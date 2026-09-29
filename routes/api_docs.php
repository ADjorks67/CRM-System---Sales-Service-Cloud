<?php

use App\Http\Controllers\Api\DocsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/api/docs', [DocsController::class, 'show'])->name('api.docs');
    Route::get('/api/docs/openapi.yaml', [DocsController::class, 'openapi'])->name('api.docs.openapi');
});

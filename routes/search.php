<?php

use App\Http\Controllers\AdvancedSearchController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

    Route::get('/search/advanced', [AdvancedSearchController::class, 'create'])->name('search.advanced.create');
    Route::post('/search/advanced', [AdvancedSearchController::class, 'store'])->name('search.advanced.run');
    Route::post('/search/advanced/save', [AdvancedSearchController::class, 'save'])->name('search.advanced.save');

    Route::get('/saved-searches', [SavedSearchController::class, 'index'])->name('saved-searches.index');
    Route::get('/saved-searches/{saved_search}', [SavedSearchController::class, 'show'])->name('saved-searches.show');
    Route::put('/saved-searches/{saved_search}', [SavedSearchController::class, 'update'])->name('saved-searches.update');
    Route::delete('/saved-searches/{saved_search}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');
});

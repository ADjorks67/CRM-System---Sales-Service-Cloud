<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavedReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/reports/saved', [SavedReportController::class, 'index'])->name('saved-reports.index');
    Route::get('/reports/saved/create', [SavedReportController::class, 'create'])->name('saved-reports.create');
    Route::post('/reports/saved', [SavedReportController::class, 'store'])->name('saved-reports.store');
    Route::get('/reports/saved/{savedReport}', [SavedReportController::class, 'show'])->name('saved-reports.show');
    Route::get('/reports/saved/{savedReport}/edit', [SavedReportController::class, 'edit'])->name('saved-reports.edit');
    Route::put('/reports/saved/{savedReport}', [SavedReportController::class, 'update'])->name('saved-reports.update');
    Route::delete('/reports/saved/{savedReport}', [SavedReportController::class, 'destroy'])->name('saved-reports.destroy');
    Route::get('/reports/saved/{savedReport}/export.csv', [SavedReportController::class, 'export'])->name('saved-reports.export');
    Route::get('/reports/saved/{savedReport}/print', [SavedReportController::class, 'print'])->name('saved-reports.print');

    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
});

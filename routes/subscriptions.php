<?php

use App\Http\Controllers\ReportSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/subscriptions', [ReportSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/saved-reports/{saved_report}/subscribe', [ReportSubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('/saved-reports/{saved_report}/subscribe', [ReportSubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}/edit', [ReportSubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{subscription}', [ReportSubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [ReportSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
});

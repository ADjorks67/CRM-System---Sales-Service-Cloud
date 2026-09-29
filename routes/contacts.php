<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::post('/contacts/bulk', [ContactController::class, 'bulk'])->name('contacts.bulk');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/{contact}/change-owner', [ContactController::class, 'changeOwner'])->name('contacts.change-owner');
});

<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::view('/', 'home')->name('home');
});

require __DIR__.'/auth.php';
require __DIR__.'/users.php';
require __DIR__.'/accounts.php';
require __DIR__.'/contacts.php';
require __DIR__.'/leads.php';

<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
});

require __DIR__.'/auth.php';
require __DIR__.'/users.php';
require __DIR__.'/accounts.php';
require __DIR__.'/contacts.php';
require __DIR__.'/leads.php';
require __DIR__.'/opportunities.php';
require __DIR__.'/cases.php';
require __DIR__.'/search.php';
require __DIR__.'/reports.php';
require __DIR__.'/events.php';
require __DIR__.'/dashboards.php';

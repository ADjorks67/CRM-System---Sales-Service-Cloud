<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::post('/home/assistant/dismiss', [HomeController::class, 'dismissAssistant'])->name('home.assistant.dismiss');
});

require __DIR__.'/auth.php';
require __DIR__.'/users.php';
require __DIR__.'/accounts.php';
require __DIR__.'/contacts.php';
require __DIR__.'/leads.php';
require __DIR__.'/opportunities.php';
require __DIR__.'/cases.php';
require __DIR__.'/tasks.php';
require __DIR__.'/imports.php';
require __DIR__.'/search.php';
require __DIR__.'/reports.php';
require __DIR__.'/events.php';
require __DIR__.'/dashboards.php';
require __DIR__.'/subscriptions.php';
require __DIR__.'/attachments.php';
require __DIR__.'/mfa.php';
require __DIR__.'/api_tokens.php';
require __DIR__.'/api_docs.php';
require __DIR__.'/gdpr.php';
require __DIR__.'/health.php';

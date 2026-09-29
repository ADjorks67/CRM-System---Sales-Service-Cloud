<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\CrmCaseController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\MetadataController;
use App\Http\Controllers\Api\V1\OpportunityController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::middleware([AuthenticateApiToken::class, 'throttle:60,1'])->prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('metadata/{object}', [MetadataController::class, 'show'])->name('metadata.show');

    Route::apiResource('leads', LeadController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('opportunities', OpportunityController::class);
    Route::apiResource('cases', CrmCaseController::class)->parameters(['cases' => 'crm_case']);
});

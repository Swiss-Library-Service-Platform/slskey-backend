<?php

use App\Http\Controllers\API\CloudAppController;
use App\Http\Controllers\API\WebhooksController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
|   Alma Webhooks
|--------------------------------------------------------------------------
*/

// Get Endpoint for initial challenge (During setup of webhook)
Route::get('/webhooks/{slskey_code}', [WebhooksController::class, 'challenge'])
    ->name('webhooks.challenge');

Route::middleware([
    'auth.webhooks',
])->group(function () {
    // POST Endpoint for Alma Webhooks
    Route::post('/webhooks/{slskey_code}', [WebhooksController::class, 'processWebhook'])
        ->name('webhooks');
});

/*
|--------------------------------------------------------------------------
|   Alma Cloud App
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth.cloudapp',
    'log.api'
])->group(function () {
    // Authenticate CloudApp
    Route::get('/cloudapp/authenticate', [CloudAppController::class, 'authenticate'])
        ->name('cloudapp.authenticate');

    // Get User Information / Activations and available SLSKey Groups
    Route::get('/cloudapp/user/{primary_id}/activate', [CloudAppController::class, 'getAvailableSlskeyGroupsWithUserActivations'])
        ->name('cloudapp.user');

    // Post User Activation
    Route::post('/cloudapp/user/{primary_id}/activate', [CloudAppController::class, 'activateUser'])
        ->name('cloudapp.activate');
});

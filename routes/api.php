<?php

use App\Http\Controllers\API\CloudAppController;
use App\Http\Controllers\API\WebhooksController;
use App\Http\Controllers\API\WebhooksProxyController;
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

// Get Endpoint for initial challenge (During setup of webhook)
Route::get('/webhooks-proxy/{slskey_code}', [WebhooksProxyController::class, 'challenge'])
    ->name('webhooks-proxy.challenge');

Route::middleware([
    'auth.webhooks',
])->group(function () {
    // POST Endpoint for Alma Webhooks
    Route::post('/webhooks-proxy/{slskey_code}', [WebhooksProxyController::class, 'processWebhook'])
        ->name('webhooks-proxy');
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

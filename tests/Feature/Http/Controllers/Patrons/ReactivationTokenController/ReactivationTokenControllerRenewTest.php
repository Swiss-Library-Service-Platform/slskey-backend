<?php

use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails renewing when token is not found', function () {
    $response = $this->get('/reactivate/invalid-token/renew');
    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('ReactivationToken/ReactivationError')
        ->where('error', __('flashMessages.errors.tokens.not_found'))
        ->where('token', 'invalid-token')
    );
});

it('fails renewing when token is not expired', function () {
    seedSlskeyActivations();

    // Get existing acitivation
    $slskeyGroup = SlskeyGroup::where('webhook_mail_activation', true)->first();
    $slskeyActivation = SlskeyActivation::query()->where('slskey_group_id', $slskeyGroup->id)->where('activated', true)->first();

    // Create Token
    $tokenService = app(\App\Services\TokenService::class);
    $slskeyGroup->webhook_mail_activation_days_token_validity = 1; // Set validity to 0 days
    $response = $tokenService->createTokenIfNotExisting($slskeyActivation->slskey_user_id, $slskeyGroup);

    // Call token endpoint
    $response = $this->get("/reactivate/{$response->token}/renew");
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('ReactivationToken/ReactivationError')
            ->where('error', __('flashMessages.errors.tokens.not_expired'));
    });
});

it('fails renewing when token activation mail is revoked', function () {
    seedSlskeyActivations();

    // Get existing acitivation
    $slskeyGroup = SlskeyGroup::where('webhook_mail_activation', false)->first();
    $slskeyActivation = SlskeyActivation::query()->where('slskey_group_id', $slskeyGroup->id)->where('activated', true)->first();

    // Revoke mail activation
    $slskeyActivation->removeWebhookActivationMail();

    // Create Token
    $tokenService = app(\App\Services\TokenService::class);
    $slskeyGroup->webhook_mail_activation_days_token_validity = 0; // Set validity to 0 days
    $response = $tokenService->createTokenIfNotExisting($slskeyActivation->slskey_user_id, $slskeyGroup);

    // Call token endpoint
    $response = $this->get("/reactivate/{$response->token}/renew");
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('ReactivationToken/ReactivationError')
            ->where('error', __('flashMessages.errors.tokens.activation_mail_revoked'));
    });
});

it('successfully renews token', function () {
    seedSlskeyActivations();

    // Mock mail service
    mockMailServiceTokenSend();

    // Get existing acitivation
    $slskeyGroup = SlskeyGroup::where('webhook_mail_activation', true)->first();
    $slskeyActivation = SlskeyActivation::query()->where('slskey_group_id', $slskeyGroup->id)->where('activated', true)->first();

    $slskeyActivation->setWebhookActivationMail('john.doe@slsp.ch');
    // Create Token
    $tokenService = app(\App\Services\TokenService::class);
    $slskeyGroup->webhook_mail_activation_days_token_validity = 0; // Set validity to 0 days
    $response = $tokenService->createTokenIfNotExisting($slskeyActivation->slskey_user_id, $slskeyGroup);

    // Call token endpoint
    $response = $this->get("/reactivate/{$response->token}/renew");
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('ReactivationToken/ReactivationRenewed');
    });
});

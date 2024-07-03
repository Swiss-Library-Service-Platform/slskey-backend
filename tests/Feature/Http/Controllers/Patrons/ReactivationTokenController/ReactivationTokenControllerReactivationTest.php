<?php

use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\SlskeyUserService;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails reactivation when token is not found', function () {
    $response = $this->get('/reactivate/invalid-token');
    $response->assertStatus(200);

    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('ReactivationToken/ReactivationError')
            ->where('error', __('flashMessages.errors.tokens.not_found'));
    });
});

it('fails because token expired', function () {
    seedSlskeyActivations();

    // Get existing acitivation
    $slskeyGroup = SlskeyGroup::where('webhook_mail_activation', true)->first();
    $slskeyActivation = SlskeyActivation::query()->where('slskey_group_id', $slskeyGroup->id)->where('activated', true)->first();

    $activationMail = 'john.doe@slsp.ch';
    $slskeyActivation->setWebhookActivationMail($activationMail);
    // Create Token
    $tokenService = app(\App\Services\TokenService::class);
    $slskeyGroup->webhook_mail_activation_days_token_validity = 0; // Set validity to 0 days
    $responseTokenService = $tokenService->createTokenIfNotExisting($slskeyActivation->slskey_user_id, $slskeyGroup);

    // Call token endpoint
    $response = $this->get($responseTokenService->reactivationLink);
    $response->assertInertia(function (AssertableInertia $page) use ($activationMail, $responseTokenService) {
        $page->component('ReactivationToken/ReactivationExpired')
            ->where('renewTokenLink', route('token.renew', ['token' => $responseTokenService->token]))
            ->where('activationMail', $activationMail);
    });
});

it('succeeds to reactivate user & show already used when activation revoked', function () {
    seedSlskeyActivations();

    // Get existing acitivation
    $slskeyGroup = SlskeyGroup::where('webhook_mail_activation', true)->first();
    $slskeyActivation = SlskeyActivation::query()->where('slskey_group_id', $slskeyGroup->id)->first();

    $lastExpirationDate = $slskeyActivation->expiration_date;
    $lastActivationDate = $slskeyActivation->activation_date;

    // Create Token
    $tokenService = app(\App\Services\TokenService::class);
    $slskeyGroup->webhook_mail_activation_days_token_validity = 1; // Set validity to 1 days
    $responseTokenService = $tokenService->createTokenIfNotExisting($slskeyActivation->slskey_user_id, $slskeyGroup);

    // sleep 1 second
    sleep(1);
    // Call token endpoint
    $response = $this->get($responseTokenService->reactivationLink);
    $slskeyActivation = SlskeyActivation::query()->where('slskey_group_id', $slskeyGroup->id)->where('slskey_user_id', $slskeyActivation->slskey_user_id)->first();

    $response->assertInertia(function (AssertableInertia $page) use ($slskeyActivation) {
        $page->component('ReactivationToken/ReactivationSuccess')
            ->where('expirationDate', $slskeyActivation->expiration_date);
    });

    // Call token endpoint again
    $response = $this->get($responseTokenService->reactivationLink);
    $response->assertInertia(function (AssertableInertia $page) use ($slskeyActivation) {
        $page->component('ReactivationToken/ReactivationSuccess')
            ->where('expirationDate', $slskeyActivation->expiration_date);
    });

    // Check if expiration date is updated
    expect($slskeyActivation->expiration_date)->not->toBe($lastExpirationDate);
    // Check if activation date is updated
    expect($slskeyActivation->activation_date)->not->toBe($lastActivationDate);

    $slskeyUser = SlskeyUser::find($slskeyActivation->slskey_user_id);
    assertUserActivationActivated($slskeyUser->primary_id, $slskeyGroup->slskey_code);

    // Deactivate user
    $slskeyUserService = app(SlskeyUserService::class);
    $slskeyUserService->deactivateSlskeyUser($slskeyUser->primary_id, $slskeyGroup->slskey_code, null, null, 'Test');
    assertUserActivationDeactivated($slskeyUser->primary_id, $slskeyGroup->slskey_code);

    // Call token endpoint again
    $response = $this->get($responseTokenService->reactivationLink);
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('ReactivationToken/ReactivationError')
            ->where('error', __('flashMessages.errors.tokens.already_used'));
    });
    assertUserActivationDeactivated($slskeyUser->primary_id, $slskeyGroup->slskey_code);
});

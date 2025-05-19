<?php

use App\Enums\WebhookResponseEnums;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    // Global setup
    mockWebhookAuth(true);
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook1';
    $this->almaInst = '41SLSP_1';
    $this->primaryId = '123456789@eduid.ch';
    $this->activationMail = 'john.doe@slsp.ch';
    $this->userData = getCreatedUserData($this->primaryId);
    $this->slskeyGroup = SlskeyGroup::where('slskey_code', $this->slskeyCode)->first();
});

it('processes a typical webhook mail activation workflow', function () {
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    // -------- Webhook ignores User without Activation Mail
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $this->userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_NO_ACTIVATION_MAIL);
    assertUserActivationMissing($this->primaryId, $this->slskeyCode);

    // -------- Webhook activates User with Activation Mail
    $this->userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $this->userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($this->primaryId, $this->slskeyCode, $this->activationMail);

    // -------- Webhook ignores User with already activated Activation Mail
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $this->userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_SAME_ACTIVATION_MAIL);
    assertUserActivationActivatedViaMail($this->primaryId, $this->slskeyCode, $this->activationMail);
    $slskeyActivation = SlskeyActivation::first();
    $expectedExpirationDate = date('Y-m-d', strtotime(now()->addDays($this->slskeyGroup->days_activation_duration)));
    $realExpirationDate = date('Y-m-d', strtotime($slskeyActivation->expiration_date));
    expect($expectedExpirationDate)->toBe($realExpirationDate);

    // -------- Before expiration, a reactivation token is sent to the user
    $this->travel($this->slskeyGroup->days_activation_duration - $this->slskeyGroup->webhook_token_reactivation_days_send_before_expiry)->days();
    mockMailServiceTokenSend();
    $tokenService = App::make(\App\Services\TokenService::class);
    $tokenServiceResponse = $tokenService->createTokenIfNotExisting($slskeyActivation->slskeyUser->id, $this->slskeyGroup, $this->activationMail, true);
    $command = $this->app->make(\App\Console\Commands\SendReactivationTokenUsers::class);
    $command->handle();

    // -------- User is deactivated on expiration date
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $this->travelBack();
    $this->travel($this->slskeyGroup->days_activation_duration + 1)->days();
    $command = $this->app->make(\App\Console\Commands\DeactivateExpiredUsers::class);
    $command->handle();
    assertUserActivationDeactivated($this->primaryId, $this->slskeyCode);

    // -------- User is reactivated with reactivation token
    $mockSwitchApiService = mockSwitchApiServiceActivation($mockSwitchApiService);
    $response = $this->get($tokenServiceResponse->reactivationLink);
    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('ReactivationToken/ReactivationSuccess')
    );
    assertUserActivationActivated($this->primaryId, $this->slskeyCode);
    $command = $this->app->make(\App\Console\Commands\DeactivateExpiredUsers::class);
    $command->handle();
    assertUserActivationActivated($this->primaryId, $this->slskeyCode);
});

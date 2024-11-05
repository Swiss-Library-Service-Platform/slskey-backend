<?php

use App\Enums\AlmaEnums;
use App\Enums\WebhookResponseEnums;
use App\Helpers\CustomWebhookVerifier\Implementations\VerifierABN;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook4';
    $this->almaInst = '41SLSP_4';
});

it('fails webhook because wrong format for verifier', function () {
    mockWebhookAuth(true);

    $primaryId = '123456789@eduid.ch';
    $userGroup = VerifierABN::USER_GROUPS[0];
    $userData = getCreatedUserData($primaryId);
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    // Activate User with wrong format
    $response->assertStatus(400);
    $response->assertSeeText(WebhookResponseEnums::ERROR_VERIFIER);
    assertUserActivationMissing($primaryId, $this->slskeyCode);
});

it('skips webhook because missing verification', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';

    // Update structure
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'] = [
        ['value' => '123456789', 'status' => 'ACTIVE'],
    ];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    // Activate but skipped
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_NON_EXISTING);
    assertUserActivationMissing($primaryId, $this->slskeyCode);
});

it('suceeds to activate - extend - deactivate', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';

    // Activate
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'] = [
        ['value' => 'a150-123456789', 'status' => 'ACTIVE'],
    ];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Extend (ignore)
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_ACTIVE);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Remove identifier (deactivate)
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $userData['webhook_user']['user']['user_identifier'] = [];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::DEACTIVATED);
    assertUserActivationDeactivated($primaryId, $this->slskeyCode);

    // Add identifier (activate)
    $mockSwitchApiService = mockSwitchApiServiceActivation($mockSwitchApiService);
    $userData['webhook_user']['user']['user_identifier'] = [
        ['value' => 'a150-123456789', 'status' => 'ACTIVE'],
    ];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Set inactive (deactivate)
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_INACTIVE);
    $userData['webhook_user']['user']['user_identifier'] = [
        ['value' => 'a150-123456789', 'status' => 'INACTIVE'],
    ];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::DEACTIVATED);

    // Set active without identifier (ignore)
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'] = [
        ['value' => 'nono-123456789', 'status' => 'ACTIVE'],
    ];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_INACTIVE_VERIFICATION);

    // Set active with identifier (activate)
    $mockSwitchApiService = mockSwitchApiServiceActivation($mockSwitchApiService);
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'] = [
        ['value' => 'a150-123456789', 'status' => 'ACTIVE'],
    ];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);
});

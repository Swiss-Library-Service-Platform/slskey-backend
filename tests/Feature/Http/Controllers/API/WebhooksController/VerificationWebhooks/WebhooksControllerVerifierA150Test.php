<?php

use App\Enums\AlmaEnums;
use App\Enums\WebhookResponseEnums;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook4';
    $this->almaInst = '41SLSP_4';
});

it('fails webhook because wrong format for verifier', function () {
    mockWebhookAuth(true);

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    // Activate User with wrong format
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_CREATION);
    assertUserActivationMissing($primaryId, $this->slskeyCode);
});

it('skips webhook because missing verification', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';

    // Update structure
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    //$userData['webhook_user']['user']['user_identifier'][0]['value'] = 'a150-123456789';
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
    $verifiedIdentifier = [
        'id_type' => [
            'value' => '01',
            'desc' => 'Identifier Type 01'
        ],
        'value' => 'a150-123456789',
        'status' => 'ACTIVE',
    ];

    // Activate
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'][0] = $verifiedIdentifier;
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
    $userData['webhook_user']['user']['user_identifier'][0] = $verifiedIdentifier;
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Set inactive (deactivate)
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_INACTIVE);
    $userData['webhook_user']['user']['user_identifier'] = [];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::DEACTIVATED);

    // Set active without identifier (ignore)
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'][0] = $verifiedIdentifier;
    $userData['webhook_user']['user']['user_identifier'][0]['status'] = 'INACTIVE';
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_INACTIVE_VERIFICATION);

    // Set active with identifier (activate)
    $mockSwitchApiService = mockSwitchApiServiceActivation($mockSwitchApiService);
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_identifier'][0] = $verifiedIdentifier;
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);
});

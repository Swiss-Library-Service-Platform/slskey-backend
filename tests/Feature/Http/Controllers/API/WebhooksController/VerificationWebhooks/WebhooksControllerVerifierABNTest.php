<?php

use App\Enums\AlmaEnums;
use App\Enums\WebhookResponseEnums;
use App\Helpers\CustomWebhookVerifier\Implementations\VerifierABN;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook3';
    $this->almaInst = '41SLSP_3';
});

it('fails webhook because missing verification', function () {
    mockWebhookAuth(true);

    $primaryId = '123456789@eduid.ch';
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getCreatedUserData($primaryId));

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_VERIFICATION);
    assertUserActivationMissing($primaryId, $this->slskeyCode);
});

it('succeeds to activate with verification - deactivate without verification', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userGroup = VerifierABN::USER_GROUPS[0];
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['user_group']['value'] = $userGroup;
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    // Activate User
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Remove User Group and Update again
    $userData = getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE);
    $userData['webhook_user']['user']['user_group']['value'] = 'External';
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);

    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    // Deactivate
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::DEACTIVATED);
    assertUserActivationDeactivated($primaryId, $this->slskeyCode);
});

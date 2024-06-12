<?php

use App\Enums\WebhookResponseEnums;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook1';
    $this->almaInst = '41SLSP_1';

    $this->activationMail = 'john.doe@slsp.ch';
});

it('ignores a user without activation mail', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getCreatedUserData($primaryId));

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_NO_ACTIVATION_MAIL);
    assertUserActivationMissing($primaryId, $this->slskeyCode);
});

it('succeeds to activate new user', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);
});

it('succeeds to activate new user - and activate again', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];

    // Activate User
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);

    // Update User without Change to Email
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_SAME_ACTIVATION_MAIL);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);
});

it('succeeds to activate new user - add another email', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];

    // Activate User
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);

    // Add another Email
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => 'nothing', 'preferred' => 'true'];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_SAME_ACTIVATION_MAIL);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);
});

it('succeeds to activate new user & remove email', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];

    // Activate User
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);

    // Add another Email
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => 'nothing', 'preferred' => 'true'];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_SAME_ACTIVATION_MAIL);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);

    // Remove Email
    $userData['webhook_user']['user']['contact_info']['email'] = [];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::REMOVED_ACTIVATION_MAIL);
    // User still active
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, null);
});

it('suceeeds to activate new user - remove email - readd email', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];

    // Activate User
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);

    // Remove Email
    $userData['webhook_user']['user']['contact_info']['email'] = [];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::REMOVED_ACTIVATION_MAIL);
    // User still active
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, null);

    // Readd Email
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);
});

it('suceeeds to activate new user - change to another activatemail', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $userData = getCreatedUserData($primaryId);
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];

    // Activate User
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);

    // Change Email
    $userData['webhook_user']['user']['contact_info']['email'] = [];

    $this->activationMail = 'john.doe@schule-1.ch';
    $userData['webhook_user']['user']['contact_info']['email'][] = ['email_address' => $this->activationMail, 'preferred' => 'true'];

    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", $userData);

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivatedViaMail($primaryId, $this->slskeyCode, $this->activationMail);
});

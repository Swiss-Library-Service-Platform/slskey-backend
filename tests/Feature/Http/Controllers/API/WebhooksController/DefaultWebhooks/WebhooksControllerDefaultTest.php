<?php

use App\Enums\AlmaEnums;
use App\Enums\WebhookResponseEnums;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\UserService;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook2';
    $this->almaInst = '41SLSP_2';
});

it('fails webhook because of validation', function () {
    mockWebhookAuth(true);

    $response = $this->post("/api/v1/webhooks/$this->slskeyCode", ['institution' => ['value' => $this->almaInst]]);
    $response->assertStatus(422);
});

it('fails webhook because of Alma Infos missing', function () {
    mockWebhookAuth(true);

    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getMissingUserInfo());
    $response->assertStatus(400);
    $response->assertSee('Alma User Error:');
});

it('fails webhook because of Activating a blocked user', function () {
    seedSlskeyActivations();
    mockSwitchApiServiceDeactivation();
    mockWebhookAuth(true);

    $slskeyGroupId = SlskeyGroup::where('slskey_code', $this->slskeyCode)->first()->id;
    $slskeyActivation = SlskeyActivation::where('slskey_group_id', $slskeyGroupId)->first();
    $slskeyUser = SlskeyUser::find($slskeyActivation->slskey_user_id);

    // Block user
    $userService = app(UserService::class);
    $response = $userService->blockSlskeyUser(
        $slskeyUser->primary_id,
        $this->slskeyCode,
        null,
        null,
        'Test'
    );
    assertUserActivationBlocked($slskeyUser->primary_id, $this->slskeyCode);

    // Activate blocked user
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getUpdatedUserData($slskeyUser->primary_id, AlmaEnums::USER_STATUS_ACTIVE));
    $response->assertStatus(400);
    $response->assertSeeText('Activation Error: '.__('flashMessages.errors.activations.user_blocked'));
    assertUserActivationBlocked($slskeyUser->primary_id, $this->slskeyCode);
});

it('ignores webhook because no edu-id', function () {
    mockWebhookAuth(true);

    $primaryId = '123';
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getCreatedUserData($primaryId));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::IGNORED_NON_EDUID);
});

it('succeeds to activate - ignore extension - deactivate - reactivate', function () {
    mockWebhookAuth(true);
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    $primaryId = '123456789@eduid.ch';
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getCreatedUserData($primaryId));

    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Ignore Extension
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getCreatedUserData($primaryId));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_ACTIVE);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Deactivate - Set User inactive
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_INACTIVE));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::DEACTIVATED);
    assertUserActivationDeactivated($primaryId, $this->slskeyCode);

    // Activate - Set User Active
    $mockSwitchApiService = mockSwitchApiServiceActivation();
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_ACTIVE));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::ACTIVATED);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Ignore Extension
    $mockSwitchApiService = mockSwitchApiServiceActivation();
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getCreatedUserData($primaryId));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_ACTIVE);
    assertUserActivationActivated($primaryId, $this->slskeyCode);

    // Delete User
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getDeletedUserData($primaryId));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::DEACTIVATED);
    assertUserActivationDeactivated($primaryId, $this->slskeyCode);

    // Deactivate User Again
    $response = $this->postJson("/api/v1/webhooks/$this->slskeyCode", getUpdatedUserData($primaryId, AlmaEnums::USER_STATUS_INACTIVE));
    $response->assertStatus(200);
    $response->assertSeeText(WebhookResponseEnums::SKIPPED_INACTIVE);
    assertUserActivationDeactivated($primaryId, $this->slskeyCode);
});

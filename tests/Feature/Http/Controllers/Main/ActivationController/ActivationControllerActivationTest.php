<?php

use App\Enums\ActivationActionEnums;
use App\Models\AlmaUser;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'man1';
});

it('fails activation because no slskeycode given', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions('man1')->create();
    $this->actingAs($user);

    $identifier = 'identifier';
    $response = $this->post("activation/$identifier");

    $response->assertStatus(403);
});

it('fails activation because no alma user', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    mockSwitchApiServiceActivation();

    $identifier = 'identifier';
    $response = activateUser($identifier, $this->slskeyCode);
    // dd($response);
    $response->assertStatus(302);
    // validate that validation failed because no alma_user
    $response->assertSessionHasErrors('alma_user');
});

it('fails activation because no edu id', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    mockSwitchApiServiceActivation();

    $identifier = 'identifier';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    $response->assertStatus(302);
    $response->assertSessionHas('error', __('flashMessages.errors.activations.no_edu_id'));
    $response->assertLocation(route('activation.preview', ['identifier' => $identifier]));
});

it('fails activation because no permissions', function () {
    $user = User::factory()->non_edu_id_password_changed()->create();
    $this->actingAs($user);

    $identifier = '123@eduid.ch';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    $response->assertStatus(302);
    $response->assertLocation(route('noroles'));

    // logout current sessions
    $this->post(route('logout'));

    $user = User::factory()->non_edu_id_password_changed()->withPermissions('man2')->create();
    $this->actingAs($user);

    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());
    $response->assertStatus(403);
});

it('fails deactivate/block/unblock/enableexp/disapleexp - user not found', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    $identifier = 'not_existing@eduid.ch';
    $response = deactivateUser($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('error', __('flashMessages.errors.activations.no_user'));

    $response = blockUser($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('error', __('flashMessages.errors.activations.no_user'));

    $response = unblockUser($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('error', __('flashMessages.errors.activations.no_user'));

    $response = disableExpiration($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('error', __('flashMessages.errors.activations.no_user'));

    $response = enableExpiration($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('error', __('flashMessages.errors.activations.no_user'));
});

it('succeeds to activate & extend', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    $identifier = '123@eduid.ch';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    // assert that session success includes  __('flashMessages.user_activated') but can contain more infos at the end
    $response->assertSessionHas('success');

    assertUserActivationActivated($identifier, $this->slskeyCode);

    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_extended');
});

it('succeeds to activate - deactivate', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    $identifier = 'identifier@eduid.ch';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    assertUserActivationActivated($identifier, $this->slskeyCode);

    $response = deactivateUser($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success', __('flashMessages.user_deactivated'));
    assertUserActivationDeactivated($identifier, $this->slskeyCode);
});

it('succeeds to activate - block - activate(error) - unblock - activate', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    $identifier = '123@eduid.ch';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    assertUserActivationActivated($identifier, $this->slskeyCode);

    $response = blockUser($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success', __('flashMessages.user_blocked'));
    assertUserActivationBlocked($identifier, $this->slskeyCode, ActivationActionEnums::BLOCKED_ACTIVE);

    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    $response->assertLocation(route('activation.preview', ['identifier' => $identifier]));
    $response->assertSessionHas('error', __('flashMessages.errors.activations.user_blocked'));
    assertUserActivationBlocked($identifier, $this->slskeyCode, ActivationActionEnums::BLOCKED_ACTIVE);

    $response = unblockUser($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success', __('flashMessages.user_unblocked'));
    assertUserActivationUnBlocked($identifier, $this->slskeyCode);

    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_reactivated');
    assertUserActivationActivated($identifier, $this->slskeyCode);
});

it('suceeds to activate - disable expiration - activate', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    $identifier = '321@eduid.ch';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());

    assertUserActivationActivated($identifier, $this->slskeyCode);

    $response = disableExpiration($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success', __('flashMessages.user_expiration_disabled'));

    assertUserActivationExpirationDisabled($identifier, $this->slskeyCode);

    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make());
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_extended');

    assertUserActivationActivated($identifier, $this->slskeyCode);

    $response = enableExpiration($identifier, $this->slskeyCode);

    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success', __('flashMessages.user_expiration_enabled'));

    assertUserActivationActivated($identifier, $this->slskeyCode);
});

it('succeeds to activate & change remark', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($this->slskeyCode)->create();
    $this->actingAs($user);

    $identifier = '123@eduid.ch';
    $firstRemark = 'first remark';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make(), $firstRemark);

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_activated');

    assertUserActivationActivated($identifier, $this->slskeyCode);
    assertUserActivationHasRemark($identifier, $this->slskeyCode, $firstRemark);

    $secondRemark = 'second remark';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make(), $secondRemark);

    assertUserActivationActivated($identifier, $this->slskeyCode);
    assertUserActivationHasRemark($identifier, $this->slskeyCode, $secondRemark);

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_extended');

    $thirdRemark = '';
    $response = activateUser($identifier, $this->slskeyCode, AlmaUser::factory()->make(), $thirdRemark);

    assertUserActivationActivated($identifier, $this->slskeyCode);
    assertUserActivationHasRemark($identifier, $this->slskeyCode, null);

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_extended');
});

/* -------------------
    Activation Helper Functions
------------------- */
function activateUser($identifier, $slskeyCode, $almaUser = null, $remark = null)
{
    mockSwitchApiServiceActivation();

    $response = test()->post("activation/$identifier", [
        'slskey_code' => $slskeyCode,
        'alma_user' => $almaUser ? $almaUser->toArray() : null,
        'remark' => $remark,
    ]);

    return $response;
}

function deactivateUser($identifier, $slskeyCode)
{
    mockSwitchApiServiceDeactivation();

    $response = test()->delete("activation/$identifier", [
        'slskey_code' => $slskeyCode,
    ]);

    return $response;
}

function blockUser($identifier, $slskeyCode)
{
    mockSwitchApiServiceDeactivation();

    $response = test()->post("activation/$identifier/block", [
        'slskey_code' => $slskeyCode,
        'remark' => 'remark',
    ]);

    return $response;
}

function unblockUser($identifier, $slskeyCode)
{
    $response = test()->delete("activation/$identifier/block", [
        'slskey_code' => $slskeyCode,
    ]);

    return $response;
}

function disableExpiration($identifier, $slskeyCode)
{
    $response = test()->post("activation/$identifier/expiration", [
        'slskey_code' => $slskeyCode,
    ]);

    return $response;
}

function enableExpiration($identifier, $slskeyCode)
{
    $response = test()->delete("activation/$identifier/expiration", [
        'slskey_code' => $slskeyCode,
    ]);

    return $response;
}

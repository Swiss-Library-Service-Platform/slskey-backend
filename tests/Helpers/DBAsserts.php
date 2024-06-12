<?php
/* -------------------
    DB Assert Helper Functions
------------------- */

use App\Enums\ActivationActionEnums;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;

function assertUserActivationMissing($identifier, $slskeyCode)
{
    test()->assertDatabaseMissing('slskey_users', [
        'primary_id' => $identifier,
    ]);
}

function assertUserActivationActivated($identifier, $slskeyCode)
{
    test()->assertDatabaseHas('slskey_users', [
        'primary_id' => $identifier,
    ]);
    test()->assertDatabaseHas('slskey_groups', [
        'slskey_code' => $slskeyCode,
    ]);
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'activated' => true,
    ]);
    test()->assertDatabaseHas('slskey_histories', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'action' => ActivationActionEnums::ACTIVATED,
    ]);
}

function assertUserActivationDeactivated($identifier, $slskeyCode)
{
    test()->assertDatabaseHas('slskey_users', [
        'primary_id' => $identifier,
    ]);
    test()->assertDatabaseHas('slskey_groups', [
        'slskey_code' => $slskeyCode,
    ]);
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'activated' => false,
        'expiration_date' => null,
    ]);
    test()->assertDatabaseHas('slskey_histories', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'action' => ActivationActionEnums::DEACTIVATED,
    ]);
}

function assertUserActivationBlocked($identifier, $slskeyCode, $action = null)
{
    test()->assertDatabaseHas('slskey_users', [
        'primary_id' => $identifier,
    ]);
    test()->assertDatabaseHas('slskey_groups', [
        'slskey_code' => $slskeyCode,
    ]);
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'blocked' => true,
    ]);
    if ($action) {
        test()->assertDatabaseHas('slskey_histories', [
            'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
            'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
            'action' => $action,
        ]);
    }
}

function assertUserActivationUnblocked($identifier, $slskeyCode)
{
    test()->assertDatabaseHas('slskey_users', [
        'primary_id' => $identifier,
    ]);
    test()->assertDatabaseHas('slskey_groups', [
        'slskey_code' => $slskeyCode,
    ]);
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'blocked' => false,
        'activated' => false,
    ]);
    test()->assertDatabaseHas('slskey_histories', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'action' => ActivationActionEnums::UNBLOCKED,
    ]);
}

function assertUserActivationExpirationDisabled($identifier, $slskeyCode)
{
    test()->assertDatabaseHas('slskey_users', [
        'primary_id' => $identifier,
    ]);
    test()->assertDatabaseHas('slskey_groups', [
        'slskey_code' => $slskeyCode,
    ]);
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'expiration_disabled' => true,
        'expiration_date' => null,
    ]);
    test()->assertDatabaseHas('slskey_histories', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'action' => ActivationActionEnums::EXPIRATION_DISABLED,
    ]);
}

function assertUserActivationActivatedViaMail($identifier, $slskeyCode, $mail)
{
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'activated' => true,
        'webhook_activation_mail' => $mail,
    ]);
}

function assertUserActivationHasRemark($identifier, $slskeyCode, $remark)
{
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'remark' => $remark,
    ]);
}

function assertUserRemindedHistory($identifier, $slskeyCode)
{
    test()->assertDatabaseHas('slskey_histories', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'action' => ActivationActionEnums::REMINDED,
    ]);
    test()->assertDatabaseHas('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'reminded' => true,
    ]);
}

function assertUserNoRemindedHistory($identifier, $slskeyCode)
{
    test()->assertDatabaseMissing('slskey_histories', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'action' => ActivationActionEnums::REMINDED,
    ]);
    test()->assertDatabaseMissing('slskey_activations', [
        'slskey_user_id' => test()->getSlskeyUser($identifier)->id,
        'slskey_group_id' => test()->getSlskeyGroupId($slskeyCode),
        'reminded' => true,
    ]);
}

function assertAdminUserExisting($identifier)
{
    test()->assertDatabaseHas('users', [
        'user_identifier' => $identifier,
    ]);
}

function getSlskeyUser($identifier)
{
    return SlskeyUser::where('primary_id', $identifier)->first();
}

function getSlskeyGroupId($slskeyCode)
{
    return SlskeyGroup::where('slskey_code', $slskeyCode)->first()->id;
}

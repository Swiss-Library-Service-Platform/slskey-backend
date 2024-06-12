<?php

namespace App\Helpers\CustomWebhookVerifier\Implementations;

use App\Helpers\CustomWebhookVerifier\CustomWebhookVerifierInterface;
use App\Models\AlmaUser;

/**
 * Class VerifyABNController
 *
 * This controller is responsible for handling the verification of ABN library for users.
 */
class VerifierBEJUNE implements CustomWebhookVerifierInterface
{
    /**
     * The user groups that are necessary.
     */
    public const USER_GROUPS = [
        'HPH_BJN_Internal', // desc: 'HPH-BJN Internal',
        'HPH_BJN_External' // desc: 'HPH-BJN External'
        //'99', // staff user,
        //'01' // swiss resident
    ];

    /**
     * Handles the verification process for ABN library.
     *
     * @param  array  $userData  The user data containing the user identifier.
     * @return bool Returns true if the user has one of the user groups, False otherwise
     */
    public static function verify(AlmaUser $almaUser): bool
    {
        // Check if user has custom group
        $user_has_group = false;
        foreach (self::USER_GROUPS as $group) {
            if ($almaUser->user_group->value == $group) {
                $user_has_group = true;
            }
        }

        return $user_has_group;
    }
}

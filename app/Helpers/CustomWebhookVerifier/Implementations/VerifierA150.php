<?php

namespace App\Helpers\CustomWebhookVerifier\Implementations;

use App\Helpers\CustomWebhookVerifier\CustomWebhookVerifierInterface;
use App\Models\AlmaUser;

/**
 * Class VerifyA150Controller
 *
 * This controller is responsible for handling the verification of A150 library for users.
 */
class VerifierA150 implements CustomWebhookVerifierInterface
{
    /**
     * The prefix used for A150 library cards.
     */
    protected const CARD_PREFIX = 'a150';

    /**
     * Handles the verification process for A150 library.
     *
     * @param  array  $userData  The user data containing the user identifier.
     * @return bool Returns true if the user has an active A150 library card, false otherwise.
     */
    public static function verify(AlmaUser $almaUser): bool
    {
        $user_has_a150_prefix = false;
        foreach ($almaUser->user_identifier as $i) {
            // Check if the card value starts with the A150 prefix and is active
            if (
                strtolower(substr($i->value, 0, 4)) === self::CARD_PREFIX
                && $i->status == 'ACTIVE'
            ) {
                $user_has_a150_prefix = true;
            }
        }

        return $user_has_a150_prefix;
    }
}

<?php

namespace App\Helpers\CustomWebhookVerifier\Implementations;

use App\Helpers\CustomWebhookVerifier\CustomWebhookVerifierInterface;
use App\Models\AlmaUser;

/**
 * Class VerifyA296Controller
 *
 * This controller is responsible for handling the verification of A296 library for users.
 */
class VerifierA296 implements CustomWebhookVerifierInterface
{
    /**
     * The prefix used for A296 library cards.
     */
    protected const CARD_PREFIX = 'a296';

    /**
     * Handles the verification process for A296 library.
     *
     * @param  array  $userData  The user data containing the user identifier.
     * @return bool Returns true if the user has an active A296 library card, false otherwise.
     */
    public static function verify(AlmaUser $almaUser): bool
    {
        $user_has_a296_prefix = false;
        foreach ($almaUser->user_identifier as $i) {
            // Check if the card value starts with the A296 prefix and is active
            if (
                strtolower(substr($i->value, 0, 4)) === self::CARD_PREFIX
                && $i->status == 'ACTIVE'
            ) {
                $user_has_a296_prefix = true;
            }
        }

        return $user_has_a296_prefix;
    }
}

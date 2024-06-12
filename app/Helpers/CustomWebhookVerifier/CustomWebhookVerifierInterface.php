<?php

namespace App\Helpers\CustomWebhookVerifier;

use App\Models\AlmaUser;

interface CustomWebhookVerifierInterface
{
    public static function verify(AlmaUser $almaUser): bool;
}

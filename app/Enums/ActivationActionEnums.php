<?php

namespace App\Enums;

class ActivationActionEnums
{
    public const ACTIVATED = 'ACTIVATED'; // User has been activated

    public const BLOCKED_ACTIVE = 'BLOCKED_ACTIVE'; // User is blocked when he was active

    public const BLOCKED_INACTIVE = 'BLOCKED_INACTIVE'; // User is blocked when he was inactive

    public const DEACTIVATED = 'DEACTIVATED'; // User has been deactivated

    public const EXTENDED = 'EXTENDED'; // User has been extended

    public const EXPIRATION_DISABLED = 'EXPIRATION_DISABLED'; // Expiration date has been disabled

    public const EXPIRATION_ENABLED = 'EXPIRATION_ENABLED'; // Expiration date has been enabled

    public const NOTIFIED = 'NOTIFIED'; // User has been notified

    public const REACTIVATED = 'REACTIVATED'; // User has been reactivated

    public const REMINDED = 'REMINDED'; // User has been reminded

    public const TOKEN_SENT = 'TOKEN_SENT'; // Token has been sent

    public const UNBLOCKED = 'UNBLOCKED'; // User is unblocked
}

<?php

namespace App\Enums;

class TriggerEnums
{
    public const WEBHOOK = 'Alma';

    public const CLOUD_APP = 'Alma Cloud App';

    public const MANUAL_UI = 'SLSKey UI';

    public const SYSTEM_EXPIRATION = 'System Expiration Job';

    public const SYSTEM_MASS_IMPORT = 'System Mass Import';

    public const SYSTEM_REMIND_EXPIRATION = 'System Reminder Job';

    public const SYSTEM_TOKEN_EXPIRATION = 'System Token Expiration Job';

    public const USER_TOKEN_REACTIVATION = 'User Token Reactivation';
}

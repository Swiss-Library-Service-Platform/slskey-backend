<?php

namespace App\Enums;

class WebhookResponseEnums
{
    // REAL ACTIONS
    public const ACTIVATED = 'Activated'; // User has been activated

    public const DEACTIVATED = 'Deactivated'; // User has been deactivated

    public const REMOVED_ACTIVATION_MAIL = 'Revoked Activation Mail'; // User lost activation mail

    // IGNORED ACTIONS
    public const IGNORED = 'Ignored'; // User has been ignored, nothing to do

    public const IGNORED_CREATION = 'Ignored: Created user misses verification'; // User has not passed the custom verifier

    public const IGNORED_NON_EDUID = 'Ignored: No edu-ID'; // User is not an edu-ID user

    public const IGNORED_NO_ACTIVATION_MAIL = 'Ignored: No Activation Mail'; // User has no activation mail

    public const IGNORED_SAME_ACTIVATION_MAIL = 'Ignored: Same Activation Mail'; // User has the same activation mail

    public const IGNORED_FLOW_ACTIVATION_MAIL = 'Ignored: Nothing to do'; // User has no activation mail

    // SKIPPED ACTIONS (Already in correct state)
    public const SKIPPED_ACTIVE = 'Skipped: User already active'; // User already in the state that it should be

    public const SKIPPED_NON_EXISTING = 'Skipped: User to deactivate does not exist in SLSKey'; // User already in the state that it should be

    public const SKIPPED_INACTIVE = 'Skipped: User to deactivate is already inactive'; // User already in the state that it should be

    public const SKIPPED_INACTIVE_VERIFICATION = 'Skipped: Unverified User already inactive'; // User already in the state that it should be

    // ERRORS
    public const ERROR_PERSISTENT = "Error: SlskeyGroup settings does not match webhook URL set in Alma"; // SlskeyGroup settings does not match webhook URL set in Alma
    
    public const ERROR_VERIFIER = 'Custom Verification Error: '; // Custom verification error

    public const ERROR_NO_INSTITUTION = 'Auth Error: No institution code provided'; // No institution code provided

    public const ERROR_NO_SLSKEY_GROUP = 'Error: No slskey group definition for this institution and Alma IZ'; // No slskey group definition for this institution and Alma IZ

    public const ERROR_INVALID_SECRET = 'Auth Error: Invalid secret'; // Invalid secret

    public const ERROR_SWITCH_ACTIVATION = 'Error: Switch activation failed'; // Switch activation failed

    public const ERROR_SWITCH_DEACTIVATION = 'Error: Switch deactivation failed'; // Switch deactivation failed
}

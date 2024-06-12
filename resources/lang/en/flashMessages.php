<?php

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
|
*/

return [
    // Admin Users
    'admin_user_created' => 'User created successfully.',
    'admin_user_deleted' => 'User deleted successfully.',
    'admin_user_password_reset' => 'Password reset successfully.',
    'admin_user_updated' => 'User updated successfully.',

    // Errors
    'errors' => [

        // Errors: Activations
        'activations' => [
            'no_activation' => 'No activation found.',
            'no_edu_id' => 'Given primary-ID is not an edu-ID.',
            "no_notify_mail_content" => "User Notify Mail is not defined correctly.",
            'no_switch_group' => 'No Switch Group found.',
            'no_user' => 'No SLSKey User found.',
            'user_blocked' => 'User is blocked.',
            'user_not_found' => 'No User found in Alma.',
        ],

        // Errors: Permissions
        'permissions_missing' => 'You have no permissions. Please contact SLSP.',

        // Errors: Tokens
        'tokens' => [
            'activation_mail_revoked' => 'The e-mail address that gave you access to SLSKey has been removed from your Switch edu-ID account. Please re-add the e-mail address to your edu-ID to get access to SLSKey.',
            'already_used' => 'Token already used.',
            'not_expired' => 'Token not expired.',
            'not_found' => 'Token not found.',
        ],
    ],

    // Publishers
    'publisher_created' => 'Publisher created successfully.',
    'publisher_deleted' => 'Publisher deleted successfully.',
    'publisher_updated' => 'Publisher updated successfully.',

    // Report Mails
    'reportmail_created' => 'Reporting mail added successfully.',
    'reportmail_deleted' => 'Reporting mail deleted successfully.',

    // SLSKey Groups
    'slskey_group_deleted' => 'User Group deleted successfully.',
    'slskey_group_updated' => 'User Group updated successfully.',
    'slskey_group_saved' => 'User Group saved successfully.',
    'switch_group_updated' => 'Switch Group updated successfully.',
    'switch_group_saved' => 'Switch Group saved successfully.',

    // SLSKey Users
    'user_activated' => 'User activated successfully.',
    'user_blocked' => 'User blocked successfully.',
    'user_deactivated' => 'User deactivated successfully.',
    'user_extended' => 'User extended successfully.',
    'user_expiration_disabled' => 'User expiration disabled successfully.',
    'user_expiration_enabled' => 'User expiration enabled successfully.',
    'user_reactivated' => 'User reactivated successfully.',
    'user_unblocked' => 'User unblocked successfully.',
];

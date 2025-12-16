<?php

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
|
*/

return [
    // Admin Users
    'admin_user_created' => 'Benutzer erfolgreich erstellt.',
    'admin_user_deleted' => 'Benutzer erfolgreich gelöscht.',
    'admin_user_password_reset' => 'Passwort erfolgreich zurückgesetzt.',
    'admin_user_updated' => 'Benutzer erfolgreich aktualisiert.',

    // Errors
    'errors' => [
        // Errors: Activations
        'activations' => [
            'no_activation' => 'Keine Aktivierung gefunden.',
            'no_edu_id' => 'Die angegebene primäre ID ist keine edu-ID.',
            "no_notify_mail_content" => "Benachrichtigungsmail des Benutzers ist nicht korrekt definiert.",
            'no_switch_group' => 'Keine Switch-Gruppe gefunden.',
            'no_user' => 'Kein SLSKey-Benutzer gefunden.',
            'user_blocked' => 'Benutzer ist blockiert.',
            'user_not_found' => 'Kein Benutzer in Alma gefunden.',
            'switch_api_error' => 'Fehler beim Aufruf der Switch-API.',
            'user_lookup_failed' => 'Benutzerinformationen können nicht abgerufen werden. Bitte versuchen Sie es erneut oder kontaktieren Sie den Support.',
        ],

        // Errors: Permissions
        'permissions_missing' => 'Sie haben keine Berechtigungen. Bitte kontaktieren Sie SLSP.',

        // Errors: Tokens
        'tokens' => [
            'activation_mail_revoked' => 'Die E-Mail-Adresse, die Ihnen Zugang zu SLSKey gegeben hat, wurde aus Ihrem Switch edu-ID-Konto entfernt. Bitte fügen Sie die E-Mail-Adresse erneut zu Ihrer edu-ID hinzu, um Zugang zu SLSKey zu erhalten.',
            'already_used' => 'Token bereits verwendet.',
            'not_expired' => 'Token nicht abgelaufen.',
            'not_found' => 'Token nicht gefunden.',
        ],
    ],

    // Publishers
    'publisher_created' => 'Verlag erfolgreich erstellt.',
    'publisher_deleted' => 'Verlag erfolgreich gelöscht.',
    'publisher_updated' => 'Verlag erfolgreich aktualisiert.',

    // Report Mails
    'reportmail_created' => 'Berichtsmail erfolgreich hinzugefügt.',
    'reportmail_deleted' => 'Berichtsmail erfolgreich gelöscht.',

    // SLSKey Groups
    'slskey_group_deleted' => 'Benutzergruppe erfolgreich gelöscht.',
    'slskey_group_updated' => 'Benutzergruppe erfolgreich aktualisiert.',
    'slskey_group_saved' => 'Benutzergruppe erfolgreich gespeichert.',
    'switch_group_updated' => 'Switch-Gruppe erfolgreich aktualisiert.',
    'switch_group_saved' => 'Switch-Gruppe erfolgreich gespeichert.',

    // SLSKey Users
    'user_activated' => 'Benutzer erfolgreich aktiviert.',
    'user_blocked' => 'Benutzer erfolgreich blockiert.',
    'user_deactivated' => 'Benutzer erfolgreich deaktiviert.',
    'user_extended' => 'Benutzer erfolgreich verlängert.',
    'user_expiration_disabled' => 'Benutzerablauf erfolgreich deaktiviert.',
    'user_expiration_enabled' => 'Benutzerablauf erfolgreich aktiviert.',
    'user_member_educational_institution_changed' => 'Die Benutzeränderung wurde erfolgreich durchgeführt.',
    'user_reactivated' => 'Benutzer erfolgreich reaktiviert.',
    'user_unblocked' => 'Benutzer erfolgreich entsperrt.',
];

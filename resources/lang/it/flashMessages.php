<?php

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
|
*/

return [
    // Admin Users
    'admin_user_created' => 'Utente creato con successo.',
    'admin_user_deleted' => 'Utente eliminato con successo.',
    'admin_user_password_reset' => 'Password reimpostata con successo.',
    'admin_user_updated' => 'Utente aggiornato con successo.',

    // Errors
    'errors' => [
        // Errors: Activations
        'activations' => [
            'no_activation' => 'Nessuna attivazione trovata.',
            'no_edu_id' => "L'ID principale fornito non è un edu-ID.",
            "no_notify_mail_content" => "Il contenuto dell'email di notifica utente non è definito correttamente.",
            'no_switch_group' => 'Nessun gruppo Switch trovato.',
            'no_user' => 'Nessun utente SLSKey trovato.',
            'user_blocked' => 'Utente bloccato.',
            'user_not_found' => 'Nessun utente trovato in Alma.',
            'switch_api_error' => "Errore durante la chiamata all'API Switch.",
        ],

        // Errors: Permissions
        'permissions_missing' => 'Non hai permessi. Si prega di contattare SLSP.',

        // Errors: Tokens
        'tokens' => [
            'activation_mail_revoked' => "L'indirizzo email che ti ha dato accesso a SLSKey è stato rimosso dal tuo account Switch edu-ID. Si prega di aggiungere nuovamente l'indirizzo email alla tua edu-ID per ottenere l'accesso a SLSKey.",
            'already_used' => 'Token già utilizzato.',
            'not_expired' => 'Token non scaduto.',
            'not_found' => 'Token non trovato.',
        ],
    ],

    // Publishers
    'publisher_created' => 'Editore creato con successo.',
    'publisher_deleted' => 'Editore eliminato con successo.',
    'publisher_updated' => 'Editore aggiornato con successo.',

    // Report Mails
    'reportmail_created' => 'Email di rapporto aggiunta con successo.',
    'reportmail_deleted' => 'Email di rapporto eliminata con successo.',

    // SLSKey Groups
    'slskey_group_deleted' => 'Gruppo utente eliminato con successo.',
    'slskey_group_updated' => 'Gruppo utente aggiornato con successo.',
    'slskey_group_saved' => 'Gruppo utente salvato con successo.',
    'switch_group_updated' => 'Gruppo Switch aggiornato con successo.',
    'switch_group_saved' => 'Gruppo Switch salvato con successo.',

    // SLSKey Users
    'user_activated' => 'Utente attivato con successo.',
    'user_blocked' => 'Utente bloccato con successo.',
    'user_deactivated' => 'Utente disattivato con successo.',
    'user_extended' => 'Utente esteso con successo.',
    'user_expiration_disabled' => "Scadenza dell'utente disabilitata con successo.",
    'user_expiration_enabled' => "Scadenza dell'utente abilitata con successo.",
    'user_member_educational_institution_changed' => "La modifica dell'utente è stata eseguita con successo.",
    'user_reactivated' => 'Utente riattivato con successo.',
    'user_unblocked' => 'Utente sbloccato con successo.',
];

<?php

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
|
*/

return [
    // Admin Users
    'admin_user_created' => 'Utilisateur créé avec succès.',
    'admin_user_deleted' => 'Utilisateur supprimé avec succès.',
    'admin_user_password_reset' => 'Mot de passe réinitialisé avec succès.',
    'admin_user_updated' => 'Utilisateur mis à jour avec succès.',

    // Errors
    'errors' => [
        // Errors: Activations
        'activations' => [
            'no_activation' => 'Aucune activation trouvée.',
            'no_edu_id' => "L'ID principal fourni n'est pas un edu-ID.",
            "no_notify_mail_content" => "Le contenu de l'e-mail de notification de l'utilisateur n'est pas correctement défini.",
            'no_switch_group' => 'Aucun groupe Switch trouvé.',
            'no_user' => "Aucun utilisateur SLSKey trouvé.",
            'user_blocked' => 'Utilisateur bloqué.',
            'user_not_found' => "Aucun utilisateur trouvé dans Alma.",
        ],

        // Errors: Permissions
        'permissions_missing' => "Vous n'avez pas les permissions. Veuillez contacter SLSP.",

        // Errors: Tokens
        'tokens' => [
            'activation_mail_revoked' => "L'adresse e-mail qui vous a donné accès à SLSKey a été retirée de votre compte Switch edu-ID. Veuillez réajouter l'adresse e-mail à votre edu-ID pour obtenir l'accès à SLSKey.",
            'already_used' => 'Jeton déjà utilisé.',
            'not_expired' => 'Jeton non expiré.',
            'not_found' => 'Jeton non trouvé.',
        ],
    ],

    // Publishers
    'publisher_created' => 'Éditeur créé avec succès.',
    'publisher_deleted' => 'Éditeur supprimé avec succès.',
    'publisher_updated' => 'Éditeur mis à jour avec succès.',

    // Report Mail
    'reportmail_created' => 'Mail de rapport ajouté avec succès.',
    'reportmail_deleted' => 'Mail de rapport supprimé avec succès.',

    // SLSKey Groups
    'slskey_group_deleted' => 'Groupe utilisateur supprimé avec succès.',
    'slskey_group_updated' => 'Groupe utilisateur mis à jour avec succès.',
    'slskey_group_saved' => 'Groupe utilisateur enregistré avec succès.',
    'switch_group_updated' => 'Groupe Switch mis à jour avec succès.',
    'switch_group_saved' => 'Groupe Switch enregistré avec succès.',

    // SLSKey Users
    'user_activated' => 'Utilisateur activé avec succès.',
    'user_blocked' => 'Utilisateur bloqué avec succès.',
    'user_deactivated' => 'Utilisateur désactivé avec succès.',
    'user_extended' => 'Utilisateur prolongé avec succès.',
    'user_expiration_disabled' => 'Expiration de l’utilisateur désactivée avec succès.',
    'user_expiration_enabled' => 'Expiration de l’utilisateur activée avec succès.',
    'user_member_educational_institution_changed' => "Le changement d'utilisateur a été effectué avec succès.",
    'user_reactivated' => 'Utilisateur réactivé avec succès.',
    'user_unblocked' => 'Utilisateur débloqué avec succès.',
];

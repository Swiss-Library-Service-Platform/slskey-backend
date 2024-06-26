<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alma API
    |--------------------------------------------------------------------------
    |
    */
    'alma' => [
        'base_url' => env('ALMA_BASE_URL', '---'),
        // API KEYs
        'api_keys' => [
            '41SLSP_NETWORK' => env('ALMA_API_NZ_KEY', '---'),
            '41SLSP_ABN' => env('ALMA_ABN_API_KEY', '---'),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | SWITCH API
    |--------------------------------------------------------------------------
    |
    */
    'switch' => [
        'base_url' => env('SWITCH_BASE_URL', '---'),
        'api_user' => env('SWITCH_API_USER', '---'),
        'api_password' => env('SWITCH_API_PASSWORD', '---'),
        'natlic_group' => env('SWITCH_NATLIC_GROUP', '---'),
    ],
];

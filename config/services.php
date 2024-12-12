<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Alma API
    |--------------------------------------------------------------------------
    |
    */
    'alma' => [
        // Base url
        'base_url' => env('ALMA_BASE_URL', '---'),
        // API KEYs
        'api_keys' => [
            '41SLSP_NETWORK' => env('ALMA_API_NZ_KEY', '---'),
            '41SLSP_ABN' => env('ALMA_ABN_API_KEY', '---'),
            '41SLSP_ZHK' => env('ALMA_ZHK_API_KEY', '---'),
            '41SLSP_ZBS' => env('ALMA_ZBS_API_KEY', '---'),
            '41SLSP_UZB' => env('ALMA_UZB_API_KEY', '---'),
            '41SLSP_UBS' => env('ALMA_UBS_API_KEY', '---'),
            '41SLSP_HPH' => env('ALMA_HPH_API_KEY', '---'),
            '41SLSP_ETH' => env('ALMA_ETH_API_KEY', '---'),
            '41SLSP_RZS' => env('ALMA_RZS_API_KEY', '---'),
            '41SLSP_BCUFR' => env('ALMA_BCUFR_API_KEY', '---'),
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

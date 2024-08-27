<?php

function getAlmaUserData(string $primaryId, ?string $status = null)
{
    return [
        'primary_id' => $primaryId,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'full_name' => 'John Doe',
        'status' => ['value' => $status ?? 'active'],
        'preferred_language' => ['value' => 'en'],
        'user_group' => ['value' => 'External'],
        'contact_info' => [
            'email' => [
                ['email_address' => 'john.doe@bluewin.ch', 'preferred' => 'true'],
            ],
            'addresses' => [
                [
                    'line1' => 'Musterstrasse 1',
                    'postal_code' => '1234',
                    'city' => 'Musterstadt',
                    'country' => ['value' => 'CH'],
                    'address_type' => ['value' => 'Home'],
                    'preferred' => 'true',
                ],
            ],
        ],
        'user_identifier' => [
            ['wrong format of identifier'],
        ],
        'record_type' => ['value' => 'STAFF'],
        'user_role' => [
            [
                'status' => [
                    "value" => "ACTIVE",
                    "desc" => "Active"
                ],
                'scope' => [
                    "value" => "x",
                    "desc" => "x"
                ],
                'role_type' => [
                    "value" => "220",
                    "desc" => "x"
                ],
                'parameter' => []
            ]
        ],
        'user_blocks' => [],
    ];
}

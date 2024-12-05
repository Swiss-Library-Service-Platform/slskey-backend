<?php

/*
    Webhook Data
*/

use App\Enums\AlmaEnums;

function getMissingUserInfo()
{
    return [
        'institution' => ['value' => test()->almaInst],
        'webhook_user' => [
            'user' => [
                'primary_id' => '123456789',
                'status' => ['value' => 'active'],
            ],
            'cause' => ['value' => 'Alma SIS'],
        ],
        'event' => ['value' => 'activation'],
    ];
}

function getCreatedUserData(string $primaryId)
{
    $user = getAlmaUserData($primaryId);

    return [
        'event' => ['value' => AlmaEnums::EVENT_CREATED],
        'institution' => ['value' => test()->almaInst],
        'webhook_user' => [
            'cause' => 'Alma SIS',
            'user' => $user
        ],
    ];
}

function getDeletedUserData(string $primaryId)
{
    $user = getAlmaUserData($primaryId);

    return [
        'event' => ['value' => AlmaEnums::EVENT_DELETED],
        'institution' => ['value' => test()->almaInst],
        'webhook_user' => [
            'cause' => 'Alma SIS',
            'user' => $user
        ],
    ];
}

function getUpdatedUserData(string $primaryId, string $status)
{
    $user = getAlmaUserData($primaryId, $status);

    return [
        'event' => ['value' => AlmaEnums::EVENT_UPDATED],
        'institution' => ['value' => test()->almaInst],
        'webhook_user' => [
            'cause' => 'Alma SIS',
            'user' =>  $user
        ],
    ];
}

<?php

namespace App\Services;

use App\DTO\TokenServiceResponse;
use App\Interfaces\SwitchAPIInterface;
use App\Models\SlskeyGroup;
use App\Models\SlskeyReactivationToken;

class TokenService
{
    public function __construct(SwitchAPIInterface $switchApiService, MailService $mailService)
    {
    }

    public function createTokenIfNotExisting(
        string $slskeyUserId,
        SlskeyGroup $slskeyGroup,
    ): TokenServiceResponse {
        $existingToken = SlskeyReactivationToken::query()
            ->where('slskey_user_id', $slskeyUserId)
            ->where('slskey_group_id', $slskeyGroup->id)
            ->where('token_used', false)
            ->first();

        if ($existingToken) {
            return new TokenServiceResponse(false, null, null, 'Token already exists.');
        }

        $token = SlskeyReactivationToken::createToken($slskeyUserId, $slskeyGroup);
        $reactivationLink = $token->getLinkFromToken();

        return new TokenServiceResponse(true, $token->token, $reactivationLink, null);
    }
}

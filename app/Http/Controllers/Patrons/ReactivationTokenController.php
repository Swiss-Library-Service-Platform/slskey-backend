<?php

namespace App\Http\Controllers\Patrons;

use App\Enums\TriggerEnums;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupPublicResource;
use App\Models\SlskeyActivation;
use App\Models\SlskeyReactivationToken;
use App\Services\MailService;
use App\Services\TokenService;
use App\Services\ActivationService;
use Inertia\Inertia;
use Inertia\Response;

class ReactivationTokenController extends Controller
{
    protected $activationService;

    protected $tokenService;

    protected $mailService;

    /**
     * ReactivationTokenController constructor.
     *
     * @param ActivationService $activationService
     * @param TokenService $tokenService
     * @param MailService $mailService
     */
    public function __construct(ActivationService $activationService, TokenService $tokenService, MailService $mailService)
    {
        $this->activationService = $activationService;
        $this->tokenService = $tokenService;
        $this->mailService = $mailService;
    }

    /**
     * Reactivate user
     *
     * @param string $token
     * @return Response
     */
    public function reactivate(string $token): Response
    {
        $slskeyReactivationToken = SlskeyReactivationToken::where('token', $token)->first();

        if (! $slskeyReactivationToken) {
            return Inertia::render('ReactivationToken/ReactivationError', [
                'error' => __('flashMessages.errors.tokens.not_found'),
                'token' => $token,
            ]);
        }

        // Check if token is expired
        // Get Email
        $slskeyActivation = SlskeyActivation::where('slskey_user_id', $slskeyReactivationToken->slskey_user_id)
            ->where('slskey_group_id', $slskeyReactivationToken->slskey_group_id)
            ->first();

        if ($slskeyReactivationToken->token_used) {
            // Link clicked twice
            // Or Outlook Safe Link prefetched the link and therefore activation was already done on the prefetch
            if ($slskeyActivation && $slskeyActivation->expiration_date) {
                return Inertia::render('ReactivationToken/ReactivationSuccess', [
                    'slskeyGroup' => SlskeyGroupPublicResource::make($slskeyReactivationToken->slskeyGroup),
                    'expirationDate' => $slskeyActivation->expiration_date,
                ]);
            }

            return Inertia::render('ReactivationToken/ReactivationError', [
                'error' => __('flashMessages.errors.tokens.already_used'),
                'token' => $token,
            ]);
        }

        if ($slskeyReactivationToken->isExpired()) {
            return Inertia::render('ReactivationToken/ReactivationExpired', [
                'renewTokenLink' => route('token.renew', ['token' => $token]),
                'activationMail' => $slskeyActivation->webhook_activation_mail,
            ]);
        }

        // Reactivate user
        $response = $this->activationService->activateSlskeyUser(
            $slskeyReactivationToken->slskeyUser->primary_id,
            $slskeyReactivationToken->slskeyGroup->slskey_code,
            null, // Author
            TriggerEnums::USER_TOKEN_REACTIVATION,
            null, // almaUser
            null // webhook activation mail
        );

        $slskeyReactivationToken->setUsed();

        // Get new activation status
        $slskeyActivation = SlskeyActivation::where('slskey_user_id', $slskeyReactivationToken->slskey_user_id)
            ->where('slskey_group_id', $slskeyReactivationToken->slskey_group_id)
            ->first();

        return Inertia::render('ReactivationToken/ReactivationSuccess', [
            'slskeyGroup' => SlskeyGroupPublicResource::make($slskeyReactivationToken->slskeyGroup),
            'expirationDate' => $slskeyActivation->expiration_date,
        ]);
    }

    /**
     * Renew reactivation token
     *
     * @param string $token
     * @return Response
     */
    public function renew(string $token): Response
    {
        $slskeyReactivationToken = SlskeyReactivationToken::where('token', $token)
            ->first();

        if (! $slskeyReactivationToken) {
            return Inertia::render('ReactivationToken/ReactivationError', [
                'error' => __('flashMessages.errors.tokens.not_found'),
                'token' => $token,
            ]);
        }

        if (! $slskeyReactivationToken->isExpired()) {
            return Inertia::render('ReactivationToken/ReactivationError', [
                'error' => __('flashMessages.errors.tokens.not_expired'),
                'token' => $token,
            ]);
        }

        // Get activation
        $slskeyActivation = SlskeyActivation::where('slskey_user_id', $slskeyReactivationToken->slskey_user_id)
            ->where('slskey_group_id', $slskeyReactivationToken->slskey_group_id)
            ->first();

        if (! $slskeyActivation->webhook_activation_mail) {
            return Inertia::render('ReactivationToken/ReactivationError', [
                'token' => $token,
                'error' => __('flashMessages.errors.tokens.activation_mail_revoked'),
            ]);
        }

        // Set old token used
        $slskeyReactivationToken->setUsed();

        // Create new tokn and send via Mail
        $response = $this->tokenService->createTokenIfNotExisting($slskeyReactivationToken->slskey_user_id, $slskeyReactivationToken->slskeyGroup);

        if (! $response->success) {
            return Inertia::render('ReactivationToken/ReactivationError', [
                'error' => $response->message,
                'token' => $token,
            ]);
        }

        // Send mail
        $this->mailService->sendReactivationTokenUserMail($slskeyReactivationToken->slskeyGroup, $slskeyActivation->webhook_activation_mail, $response->reactivationLink);

        return Inertia::render('ReactivationToken/ReactivationRenewed', [
            'slskeyGroup' => SlskeyGroupPublicResource::make($slskeyReactivationToken->slskeyGroup),
        ]);
    }
}

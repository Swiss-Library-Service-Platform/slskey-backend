<?php

namespace App\Http\Controllers\API;

use App\Enums\AlmaEnums;
use App\Enums\TriggerEnums;
use App\Enums\WebhookResponseEnums;
use App\Helpers\WebhookMailActivation\WebhookMailActivationHelper;
use App\Http\Controllers\Controller;
use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\ActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

/**
 * This controller handles Alma Webhooks for the application.
 */
class WebhooksController extends Controller
{
    protected $activationService;

    /**
     * WebhooksController constructor.
     *
     * @param  ActivationService  $activationService
     */
    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
    }

    /**
     * Responds to challenge requests from webhooks.
     *
     * @return JsonResponse
     */
    public function challenge(): JsonResponse
    {
        return new JsonResponse(['challenge' => Request::input('challenge')]);
    }

    /**
     * Validates the webhook request.
     *
     * @throws ValidationException
     */
    protected function validateWebhookRequest(): void
    {
        Request::validate([
            'institution.value' => 'required',
            'webhook_user.user.primary_id' => 'required',
            'webhook_user.user.status.value' => 'required',
            'webhook_user.cause' => 'required',
            'event.value' => 'required',
        ]);
    }

    /**
     * Processes the incoming webhook.
     *
     * @return Response
     */
    public function processWebhook(): Response
    {
        try {
            $this->validateWebhookRequest();
        } catch (ValidationException $e) {
            return new Response(['errors' => $e->validator->errors()], 422);
        }

        $slskeyCode = Request::route()->parameter('slskey_code');
        $primaryId = Request::input('webhook_user.user.primary_id');

        // Build Alma User
        try {
            $almaUser = AlmaUser::fromApiResponse(json_decode(Request::getContent())->webhook_user->user);
        } catch (\Exception $e) {
            return response('Alma User Error: '.$e->getMessage(), 400);
        }

        // Ignore non-eduID Alma Users
        if (! SlskeyUser::isPrimaryIdEduId($primaryId)) {
            return response(WebhookResponseEnums::IGNORED_NON_EDUID);
        }

        // Handle Activation based on Email Domain
        $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)->first();
        if ($slskeyGroup->webhook_mail_activation) {
            return $this->handleEmailDomainActivation($slskeyGroup, $almaUser);
        }

        // Handle Activation or Deactivation
        return $this->handleActivationOrDeactivation($slskeyGroup, $almaUser);
    }

    /**
     * Handles email domain activation.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @return Response
     */
    protected function handleEmailDomainActivation(SlskeyGroup $slskeyGroup, AlmaUser $almaUser): Response
    {
        $webhookMailActivationHelper = new WebhookMailActivationHelper($slskeyGroup->webhook_mail_activation_domains);
        $almaUserWebhookActivationMail = $webhookMailActivationHelper->getWebhookActivationMail($almaUser);

        $slskeyUser = SlskeyUser::where('primary_id', $almaUser->primary_id)->first();
        $slskeyUserWebhookActivationMail = $slskeyUser?->getWebhookActivationMail($slskeyGroup->id);

        // Skip: if AlmaData has no activation mail and SlskeyUser has no activation mail
        if (! $almaUserWebhookActivationMail && ! $slskeyUserWebhookActivationMail) {
            return response(WebhookResponseEnums::IGNORED_NO_ACTIVATION_MAIL);
        }

        // Skip: if both have the same activation mail
        if ($slskeyUserWebhookActivationMail === $almaUserWebhookActivationMail) {
            return response(WebhookResponseEnums::IGNORED_SAME_ACTIVATION_MAIL);
        }

        // New User
        if ($almaUserWebhookActivationMail && ! $slskeyUserWebhookActivationMail) {
            $response = $this->handleActivation($slskeyGroup, $almaUser, $almaUserWebhookActivationMail);

            return $response;
        }

        // Existing User
        if ($almaUserWebhookActivationMail &&
            $slskeyUserWebhookActivationMail &&
            $slskeyUserWebhookActivationMail !== $almaUserWebhookActivationMail
        ) {
            $response = $this->handleActivation($slskeyGroup, $almaUser, $almaUserWebhookActivationMail);

            return $response;
        }

        // Existing User lost activation mail
        if (! $almaUserWebhookActivationMail && $slskeyUserWebhookActivationMail) {
            // Deaktivieren der Mail und Skip Activation/Deactivation
            $slskeyUser->removeWebhookActivationMail($slskeyGroup->id);

            return response(WebhookResponseEnums::REMOVED_ACTIVATION_MAIL);
        }

        return response(WebhookResponseEnums::IGNORED_FLOW_ACTIVATION_MAIL);
    }

    /**
     * Handles activation or deactivation based on the event.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @return Response
     */
    protected function handleActivationOrDeactivation(SlskeyGroup $slskeyGroup, AlmaUser $almaUser): Response
    {
        $event = Request::input('event.value');
        $status = Request::input('webhook_user.user.status.value');
        $primaryId = Request::input('webhook_user.user.primary_id');
        $slskeyUser = SlskeyUser::where('primary_id', $primaryId)->first();

        // Check if user has custom verification
        try {
            $hasCustomVerification = $slskeyGroup->checkCustomVerificationForUser($almaUser);
        } catch (\Exception $e) {
            return response(WebhookResponseEnums::ERROR_VERIFIER.$e->getMessage(), 400);
        }

        // Check for Activation
        if (
            ($event === AlmaEnums::EVENT_CREATED && $hasCustomVerification) ||
            ($event === AlmaEnums::EVENT_UPDATED && $status === AlmaEnums::USER_STATUS_ACTIVE && $hasCustomVerification)
        ) {
            // Check if already active
            if ($slskeyUser && $slskeyUser->hasActiveActivation($slskeyGroup->id)) {
                return response(WebhookResponseEnums::SKIPPED_ACTIVE);
            }

            return $this->handleActivation($slskeyGroup, $almaUser);
        }

        // Check for Deactivation
        if (
            $event === AlmaEnums::EVENT_DELETED ||
            ($event === AlmaEnums::EVENT_UPDATED && $status === AlmaEnums::USER_STATUS_INACTIVE) ||
            ($event === AlmaEnums::EVENT_UPDATED && ! $hasCustomVerification)
        ) {
            return $this->handleDeactivation($slskeyGroup, $almaUser, $hasCustomVerification);
        }

        // Nothing happened
        return new Response(WebhookResponseEnums::IGNORED_VERIFICATION);
    }

    /**
     * Handles user activation.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @param string|null $webhookActivationMail
     * @return Response
     */
    protected function handleActivation(SlskeyGroup $slskeyGroup, AlmaUser $almaUser, string $webhookActivationMail = null): Response
    {
        $primaryId = Request::input('webhook_user.user.primary_id');
        $cause = Request::input('webhook_user.cause');
        $institution = Request::input('institution.value');

        $trigger = TriggerEnums::WEBHOOK.' '.$cause.' '.$institution;
        $response = $this->activationService->activateSlskeyUser(
            $primaryId,
            $slskeyGroup->slskey_code,
            $trigger,
            null, // author
            $almaUser,
            $webhookActivationMail
        );

        if (! $response->success) {
            return response('Activation Error: '. $response->message, 400);
        }

        return new Response(WebhookResponseEnums::ACTIVATED);
    }

    /**
     * Handles user deactivation.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @param boolean $hasCustomVerification
     * @return Response
     */
    protected function handleDeactivation(SlskeyGroup $slskeyGroup, AlmaUser $almaUser, bool $hasCustomVerification): Response
    {
        $slskeyUser = SlskeyUser::where('primary_id', Request::input('webhook_user.user.primary_id'))->first();

        $primaryId = Request::input('webhook_user.user.primary_id');
        $cause = Request::input('webhook_user.cause');
        $institution = Request::input('institution.value');

        if (! $hasCustomVerification && (! $slskeyUser || ! $slskeyUser->hasActiveActivation($slskeyGroup->id))) {
            return new Response(WebhookResponseEnums::SKIPPED_INACTIVE_VERIFICATION);
        }

        if (! $slskeyUser || ! $slskeyUser->hasActiveActivation($slskeyGroup->id)) {
            return new Response(WebhookResponseEnums::SKIPPED_INACTIVE);
        }

        $trigger = TriggerEnums::WEBHOOK.' '.$cause.' '.$institution;
        $response = $this->activationService->deactivateSlskeyUser(
            $primaryId,
            $slskeyGroup->slskey_code,
            '',
            null,
            $trigger
        );

        if (! $response->success) {
            return new Response('Deactivation Error: '. $response->message, 400);
        }

        return new Response(WebhookResponseEnums::DEACTIVATED);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Enums\AlmaEnums;
use App\Enums\WebhookResponseEnums;
use App\Http\Controllers\Controller;
use App\Interfaces\SwitchAPIInterface;
use App\Models\LogActivationFails;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

/**
 * This controller handles Alma Webhooks for the application.
 */
class WebhooksProxyController extends Controller
{
    protected $switchApiService;

    /**
     * WebhooksProxyController constructor.
     *
     * @param SwitchAPIInterface  $switchApiService
     */
    public function __construct(SwitchAPIInterface $switchApiService)
    {
        $this->switchApiService = $switchApiService;
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

        // Pathparameter slskey_code
        $slskeyCode = Request::route()->parameter('slskey_code');

        // Alma User Data
        $primaryId = Request::input('webhook_user.user.primary_id');
        $userStatus = Request::input('webhook_user.user.status.value');
        $event = Request::input('event.value');

        // Check slskeygroup persistent
        $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)->first();
        if ($slskeyGroup->webhook_persistent) {
            return response(WebhookResponseEnums::ERROR_PERSISTENT);
        }

        // Ignore non-eduID Alma Users
        if (! SlskeyUser::isPrimaryIdEduId($primaryId)) {
            return response(WebhookResponseEnums::IGNORED_NON_EDUID);
        }

        // Activate user
        if ($event == AlmaEnums::EVENT_CREATED ||
            $event == AlmaEnums::EVENT_UPDATED && $userStatus == AlmaEnums::USER_STATUS_ACTIVE) {
            return $this->activateUser($primaryId, $slskeyGroup);
            // Deactivate user
        } elseif ($event == AlmaEnums::EVENT_DELETED ||
            $event == AlmaEnums::EVENT_UPDATED && $userStatus == AlmaEnums::USER_STATUS_INACTIVE) {
            return $this->deactivateUser($primaryId, $slskeyGroup);
        }

        // Ignored
        return response(WebhookResponseEnums::IGNORED);
    }

    /**
     * Activates a user.
     *
     * @param string $primaryId
     * @param SlskeyGroup $slskeyGroup
     */
    protected function activateUser(string $primaryId, SlskeyGroup $slskeyGroup): Response
    {
        try {
            // Activate user for all switch groups
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                $this->switchApiService->activatePublisherForUser($primaryId, $switchGroup->switch_group_id);
            }
        } catch (Exception $e) {
            // Log error
            $this->logError($primaryId, $e->getMessage());

            return response(WebhookResponseEnums::ERROR_SWITCH_ACTIVATION);
        }

        return response(WebhookResponseEnums::ACTIVATED);
    }

    /*
    * Deactivates a user.
    *
    * @param string $primaryId
    * @param SlskeyGroup $slskeyGroup
    */
    protected function deactivateUser(string $primaryId, SlskeyGroup $slskeyGroup): Response
    {
        try {
            // Deactivate user for all switch groups
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $switchGroup->switch_group_id);
            }
        } catch (Exception $e) {
            // Log error
            $this->logError($primaryId, $e->getMessage());

            return response(WebhookResponseEnums::ERROR_SWITCH_DEACTIVATION);
        }

        return response(WebhookResponseEnums::DEACTIVATED);
    }

    /*
    * Logs an error.
    *
    * @param string $primaryId
    * @param string $message
    */
    protected function logError(string $primaryId, string $message): void
    {
        LogActivationFails::create([
            'primary_id' => $primaryId,
            'action' => 'switch_api_error',
            'message' => $message,
            'author' => null
        ]);
    }
}

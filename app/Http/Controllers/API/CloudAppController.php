<?php

namespace App\Http\Controllers\API;

use App\Enums\TriggerEnums;
use App\Http\Controllers\Controller;
use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use App\Services\SlskeyUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class CloudAppController extends Controller
{
    protected $slskeyUserService;

    /**
     * CloudAppController constructor.
     *
     * @param SlskeyUserService $slskeyUserService
     */
    public function __construct(SlskeyUserService $slskeyUserService)
    {
        $this->slskeyUserService = $slskeyUserService;
    }

    /**
     * Authenticate user
     * Main functionality is done in middleware (AuthCloudApp)
     *
     * @return JsonResponse
     */
    public function authenticate(): JsonResponse
    {
        return new JsonResponse('Authenticated', 200);
    }

    /**
     * Get available slskey groups with user activations
     *
     * @return JsonResponse
     */
    public function getAvailableSlskeyGroupsWithUserActivations(): JsonResponse
    {
        $primaryId = Request::route()->parameter('primary_id');

        // Get Institution code from session (Set by middleware)
        $instCode = session('alma_institution');
        $slskeyGroups = SlskeyGroup::getPermittedGroupsWithUserActivations($primaryId, $instCode);

        return new JsonResponse($slskeyGroups);
    }

    /**
     * Activate user
     *
     * @return JsonResponse
     */
    public function activateUser(): JsonResponse
    {
        // validate input slskey_code and remark
        Request::validate([
            'slskey_code' => ['required', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:255'],
            'alma_user' => ['required'],
        ]);
        $slskeyCode = Request::input('slskey_code');
        $remark = Request::input('remark');
        $primaryId = Request::route()->parameter('primary_id');

        // Assuming $jsonData contains your JSON data
        $almaUserInput = $this->convertArrayToObject(Request::input('alma_user'));
        $almaUser = AlmaUser::fromApiResponse($almaUserInput);

        // Activate user via SWITCH API
        $response = $this->slskeyUserService->activateSlskeyUser(
            $primaryId,
            $slskeyCode,
            Auth::user()->display_name,
            TriggerEnums::CLOUD_APP,
            $almaUser,
            null // webhook activation mail
        );

        if (! $response->success) {
            return new JsonResponse('Activation Error: '. $response->message, 400);
        }

        // Set Remark
        $this->slskeyUserService->setActivationRemark($primaryId, $slskeyCode, $remark);

        return new JsonResponse($response->message);
    }

    /**
     * Convert array to object
     *
     * @param array|string $data
     * @return object|string
     */
    private function convertArrayToObject(array|string|null $data): object|string|null
    {
        if (is_array($data)) {
            return (object) array_map([$this, 'convertArrayToObject'], $data);
        } else {
            return $data;
        }
    }
}

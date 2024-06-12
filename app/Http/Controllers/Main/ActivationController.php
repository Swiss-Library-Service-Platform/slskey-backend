<?php

namespace App\Http\Controllers\Main;

use App\Enums\TriggerEnums;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyUserResource;
use App\Interfaces\AlmaAPIInterface;
use App\Interfaces\SwitchAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivationController extends Controller
{
    protected $almaApiService;

    protected $switchApiService;

    protected $userService;

    /**
     * ActivationController constructor.
     *
     * @param AlmaAPIInterface $almaApiService
     * @param SwitchAPIInterface $switchApiService
     * @param UserService $userService
     */
    public function __construct(AlmaAPIInterface $almaApiService, SwitchAPIInterface $switchApiService, UserService $userService)
    {
        $this->almaApiService = $almaApiService;
        $this->switchApiService = $switchApiService;
        $this->userService = $userService;
    }

    /**
     * Start route for Activation
     *
     * @return Response
     */
    public function start(): Response
    {
        return Inertia::render('Activation/ActivationStart', []);
    }

    /**
     * Preview route for Activation
     *
     * @param string $identifier
     * @return Response|RedirectResponse
     */
    public function preview(string $identifier): Response|RedirectResponse
    {
        // Selected SLSKey Code
        // $selectedSlskeyCode = Request::input('slskey_code');
        $origin = Request::input('origin');

        // Alma User Details
        $almaServiceResponse = $this->almaApiService->getUserByIdentifier($identifier);
        if (! $almaServiceResponse->success) {
            if ($origin != 'ACTIVATION_START') {
                return Redirect::route('users.show', $identifier)->with('error', $almaServiceResponse->errorText);
            }

            return Redirect::route('activation.start')->with('error', $almaServiceResponse->errorText);
        }

        // SLSKey User (stored in SLSKey database)
        $almaUser = $almaServiceResponse->almaUser;
        $slskeyUser = SlskeyUser::query()
            ->where('primary_id', $almaUser->primary_id)
            ->whereHasPermittedActivations()
            ->withPermittedActivations()
            ->firstOr(function () {
                return null;
            });

        if ($slskeyUser && $origin === 'ACTIVATION_START') {
            // redirect to user details
            return Redirect::route('users.show', $slskeyUser->primary_id);
        }

        // SLSKey Groups
        $slskeyGroups = SlskeyGroup::getPermittedGroupsWithUserActivations($almaUser->primary_id);
        // Preselect SLSKey Code, if only 1 available group, and group is not blocked
        $preselectedSlskeyCode = count($slskeyGroups) != 1 ? null : (! $slskeyGroups[0]['activation'] || ! $slskeyGroups[0]['activation']['blocked'] ? $slskeyGroups[0]['value'] : null);

        return Inertia::render('Activation/ActivationPreview', [
            'identifier' => $identifier,
            'almaUser' => $almaUser,
            'slskeyGroups' => $slskeyGroups,
            'slskeyUser' => $slskeyUser ? SlskeyUserResource::make($slskeyUser) : null,
            'preselectedSlskeyCode' => $preselectedSlskeyCode,
            'origin' => $origin,
        ]);
    }

    /**
     * Activate route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function activate(string $primaryId): RedirectResponse
    {
        // validate input slskey_code and remark
        Request::validate([
            'slskey_code' => ['required', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:255'],
            'alma_user' => ['required', 'array'],
        ]);
        $slskeyCode = Request::input('slskey_code');
        $remark = Request::input('remark');
        $almaUser = AlmaUser::fromJsonObject(Request::input('alma_user'));

        // Activate user via SWITCH API
        $response = $this->userService->activateSlskeyUser(
            $primaryId,
            $slskeyCode,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI,
            $almaUser,
            null // webhook activation mail
        );

        // Error handling
        if (! $response->success) {
            return Redirect::route('activation.preview', $primaryId)->with('error', $response->message);
        }

        // Set Remark
        $this->userService->setActivationRemark($primaryId, $slskeyCode, $remark);

        // redirect ro users.show with success message flash message from activated array
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }

    /**
     * Deactivate route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function deactivate(string $primaryId): RedirectResponse
    {
        $slskeyCode = Request::input('slskey_code');
        $remark = Request::input('remark');

        // Activate user via SWITCH API
        $response = $this->userService->deactivateSlskeyUser(
            $primaryId,
            $slskeyCode,
            $remark,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (! $response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }

    /**
     * Block route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function block(string $primaryId): RedirectResponse
    {
        $slskeyCode = Request::input('slskey_code');
        $remark = Request::input('remark');

        // Activate user via SWITCH API
        $response = $this->userService->blockSlskeyUser(
            $primaryId,
            $slskeyCode,
            $remark,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (! $response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }

    /**
     * Unblock route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function unblock(string $primaryId): RedirectResponse
    {
        $slskeyCode = Request::input('slskey_code');
        $remark = Request::input('remark');

        // Activate user via SWITCH API
        $response = $this->userService->unblockSlskeyUser(
            $primaryId,
            $slskeyCode,
            $remark,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (! $response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }

    /**
     * Disable Expiration route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function disableExpiration(string $primaryId): RedirectResponse
    {
        $slskeyCode = Request::input('slskey_code');

        // Activate user via SWITCH API
        $response = $this->userService->disableExpirationSlskeyUser(
            $primaryId,
            $slskeyCode,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (! $response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }

    /**
     * Enable Expiration route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function enableExpiration(string $primaryId): RedirectResponse
    {
        $slskeyCode = Request::input('slskey_code');

        // Activate user via SWITCH API
        $response = $this->userService->enableExpirationSlskeyUser(
            $primaryId,
            $slskeyCode,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (! $response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }
}

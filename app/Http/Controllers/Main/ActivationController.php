<?php

namespace App\Http\Controllers\Main;

use App\Enums\TriggerEnums;
use App\Enums\WorkflowEnums;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyUserResource;
use App\Interfaces\AlmaAPIInterface;
use App\Interfaces\SwitchAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\ActivationService;
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

    protected $activationService;

    /**
     * ActivationController constructor.
     *
     * @param AlmaAPIInterface $almaApiService
     * @param SwitchAPIInterface $switchApiService
     * @param ActivationService $ActivationService
     */
    public function __construct(AlmaAPIInterface $almaApiService, SwitchAPIInterface $switchApiService, ActivationService $activationService)
    {
        $this->almaApiService = $almaApiService;
        $this->switchApiService = $switchApiService;
        $this->activationService = $activationService;
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
        /** @var \App\Models\User */
        $user = Auth::user();

        // Origin: either ACTIVATION_START or null (if coming from user detail page)
        $origin = Request::input('origin');

        // Get Alma Users from all permitted IZs
        $slskeyGroups = SlskeyGroup::wherePermissions()->get()->pluck('alma_iz')->unique()->toArray();
        $almaServiceResponse = $this->almaApiService->getUserFromMultipleIzs($identifier, $slskeyGroups);
        if (!$almaServiceResponse->success) {
            // Use error message from service (already user-friendly and logged)
            if ($origin != 'ACTIVATION_START') {
                return Redirect::route('users.show', $identifier)->with('error', $almaServiceResponse->errorText);
            }

            return Redirect::route('activation.start')->with('error', $almaServiceResponse->errorText);
        }
        $almaUsers = $almaServiceResponse->almaUsers;

        // SLSKey User (stored in SLSKey database)
        $primaryId = $almaServiceResponse->almaUsers[0]->primary_id;
        $slskeyUser = SlskeyUser::query()
            ->filter([])
            ->where('primary_id', $primaryId)
            ->withPermittedActivations()
            ->firstOr(function () {
                return null;
            });

        if ($slskeyUser && $origin === 'ACTIVATION_START') {
            // redirect to user details
            return Redirect::route('users.show', $slskeyUser->primary_id);
        }

        // SLSKey Groups
        $slskeyGroups = SlskeyGroup::getPermittedGroupsWithUserActivations($primaryId);

        // Preselect SLSKey Code, if only 1 available group, not webhook, and group is not blocked
        $preselectedSlskeyCode = null;
        if (count($slskeyGroups) == 1) {
            $group = $slskeyGroups[0];
            $activation = $group['activation'];
            if ((! $activation || ! $activation['blocked']) && $group['workflow'] !== WorkflowEnums::WEBHOOK) {
                $preselectedSlskeyCode = $group['value'];
            }
        }

        return Inertia::render('Activation/ActivationPreview', [
            'identifier' => $identifier,
            'almaUsers' => $almaUsers,
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
            'member_educational_institution' => ['integer', 'nullable'],
            'alma_user' => ['required', 'array'],
        ]);
        $slskeyCode = Request::input('slskey_code');
        $remark = Request::input('remark');
        $almaUser = AlmaUser::fromJsonObject(Request::input('alma_user'));
        $isMemberOfEducationInstitution = Request::input('member_educational_institution') ?? false;

        // Activate user via SWITCH API
        $response = $this->activationService->activateSlskeyUser(
            $primaryId,
            $slskeyCode,
            TriggerEnums::MANUAL_UI,
            Auth::user()->user_identifier,
            $almaUser,
            null // webhook activation mail
        );

        // Error handling
        if (!$response->success) {
            return Redirect::route('activation.preview', $primaryId)->with('error', $response->message);
        }

        // Set Remark
        $this->activationService->setActivationRemark($primaryId, $slskeyCode, $remark);

        // Set Member Educational Institution
        $this->activationService->setActivationMemberEducationalInstitution($primaryId, $slskeyCode, $isMemberOfEducationInstitution);

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
        $response = $this->activationService->deactivateSlskeyUser(
            $primaryId,
            $slskeyCode,
            $remark,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (!$response->success) {
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
        $response = $this->activationService->blockSlskeyUser(
            $primaryId,
            $slskeyCode,
            $remark,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (!$response->success) {
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
        $response = $this->activationService->unblockSlskeyUser(
            $primaryId,
            $slskeyCode,
            $remark,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (!$response->success) {
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
        $response = $this->activationService->disableExpirationSlskeyUser(
            $primaryId,
            $slskeyCode,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (!$response->success) {
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
        $response = $this->activationService->enableExpirationSlskeyUser(
            $primaryId,
            $slskeyCode,
            Auth::user()->user_identifier,
            TriggerEnums::MANUAL_UI
        );

        // Error handling
        if (!$response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }

    /**
     * Set Member Educational Institution route for Activation
     *
     * @param string $primaryId
     * @return RedirectResponse
     */
    public function changeMemberEducationalInstitution(string $primaryId): RedirectResponse
    {
        $slskeyCode = Request::input('slskey_code');
        $memberEducationalInstitution = Request::input('member_educational_institution');

        // Activate user via SWITCH API
        $response = $this->activationService->setActivationMemberEducationalInstitution(
            $primaryId,
            $slskeyCode,
            $memberEducationalInstitution,
        );

        // Error handling
        if (!$response->success) {
            return Redirect::route('users.show', $primaryId)->with('error', $response->message);
        }

        // Redirect to user
        return Redirect::route('users.show', $primaryId)->with('success', $response->message);
    }
}

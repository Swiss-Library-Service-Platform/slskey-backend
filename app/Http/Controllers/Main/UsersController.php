<?php

namespace App\Http\Controllers\Main;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlskeyGroupSelectResource;
use App\Http\Resources\SlskeyUserDetailResource;
use App\Http\Resources\SlskeyUserResource;
use App\Interfaces\AlmaAPIInterface;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UsersController extends Controller
{
    protected $almaApiService;

    protected $userService;

    /**
     * UsersController constructor.
     */
    public function __construct(AlmaAPIInterface $almaApiService, UserService $userService)
    {
        $this->almaApiService = $almaApiService;
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     *
     * @return Response
     */
    public function index(): Response
    {
        // Set the number of items to display per page
        $perPage = intval(Request::input('perPage', 10));
        $slskeyCode = Request::input('slskeyCode');

        // Query for SlskeyGroups with specified permissions
        $slskeyGroups = SlskeyGroup::query()
            ->wherePermissions()
            ->get();

        // Query for SlskeyUsers with specified permissions, ordering, and filtering
        $slskeyUsersWithPermissions = SlskeyUser::query()
            ->whereHasPermittedActivations($slskeyCode)
            ->withPermittedActivations($slskeyCode)
            ->filter(Request::all())
            ->paginate($perPage)
            ->withQueryString();

        // If search filter is set and no results are found, query for Alma users
        if (Request::input('search') && $slskeyUsersWithPermissions->isEmpty()) {
            $almaServiceResponse = $this->almaApiService->getUserByIdentifier(Request::input('search'));
            $almaUser = $almaServiceResponse->almaUser;
            if ($almaUser) {
                $slskeyUsersWithPermissions = SlskeyUser::query()
                    ->where('primary_id', $almaUser->primary_id)
                    ->whereHasPermittedActivations($slskeyCode)
                    ->withPermittedActivations($slskeyCode)
                    ->filter(Request::except('search'))
                    ->paginate($perPage)
                    ->withQueryString();
            }
        }

        // Render the UsersIndex Inertia view with the necessary data
        return Inertia::render('Users/UsersIndex', [
            'perPage' => $perPage,
            'filters' => Request::all(),
            'slskeyUsers' => SlskeyUserResource::collection($slskeyUsersWithPermissions),
            'slskeyGroups' => SlskeyGroupSelectResource::collection($slskeyGroups),
        ]);
    }

    /**
     * Display the specified user.
     *
     * @return Response
     */
    public function show(string $identifier): Response
    {
        // Query for SLSKey user by primary ID
        $user = SlskeyUser::query()
            ->where('primary_id', $identifier)
            ->whereHasPermittedActivations()
            ->withPermittedActivations()
            ->withPermittedHistories()
            ->first();

        // Abort with 404 if the user is not found
        if (! $user) {
            abort(404);
        }

        // Check if there is a slskeyGroup in users slskeyActviation which has 'webhook_mail_activation' set
        $webhookMailActivation = $user->slskeyActivations->first(function ($activation) {
            return $activation->slskeyGroup->webhook_mail_activation;
        });

        // Render the UserDetail Inertia view with SlskeyUser and AlmaUser data
        return Inertia::render('Users/Detail/UserDetail', [
            'slskeyUser' => new SlskeyUserDetailResource($user),
            'isWebhookMailActivation' => $webhookMailActivation ? true : false,
        ]);
    }

    /**
     * Display the specified user.
     *
     * @return JsonResponse
     */
    public function getAndUpdateAlmaUserInfos(string $identifier): JsonResponse
    {
        // Query for SLSKey user by primary ID
        $slskeyUser = SlskeyUser::query()
            ->where('primary_id', $identifier)
            ->whereHasPermittedActivations()
            ->withPermittedActivations()
            ->withPermittedHistories()
            ->firstOrFail();

        // Get Alma user data using the AlmaAPIInterface
        $almaServiceResponse = $this->almaApiService->getUserByIdentifier($identifier);
        $almaUser = $almaServiceResponse->almaUser;

        if ($almaUser) {
            $slskeyUser->updateUserDetails($almaUser);
        }

        // Render the UserDetail Inertia view with SlskeyUser and AlmaUser data
        return new JsonResponse([
            'almaUser' => $almaUser,
        ]);
    }

    /**
     * Export the users list to an Excel file.
     *
     * @return BinaryFileResponse
     */
    public function exportList(): BinaryFileResponse
    {
        return Excel::download(new UsersExport(Request::all()), 'slskey_users.xlsx');
    }

    /**
     * Get the switch status of the specified user.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @return JsonResponse
     */
    public function getSwitchStatus(string $primaryId, string $slskeyCode): JsonResponse
    {
        // Query for SLSKey user by primary ID
        $user = SlskeyUser::query()
            ->where('primary_id', $primaryId)
            ->whereHasPermittedActivations()
            ->withPermittedActivations()
            ->withPermittedHistories()
            ->first();

        // Abort with 404 if the user is not found
        if (! $user) {
            abort(404);
        }

        // Query for SLSKey user by primary ID
        $response = $this->userService->verifySwitchStatusSlskeyUser($primaryId, $slskeyCode);

        return new JsonResponse([
            'status' => $response->success,
        ]);
    }
}

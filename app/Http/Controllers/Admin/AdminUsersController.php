<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\SlskeyGroupAdminResource;
use App\Http\Resources\SlskeyGroupSelectResource;
use App\Models\SlskeyGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminUsersController extends Controller
{
    /**
     * Index route for Admin Users
     *
     * @return Response
     */
    public function index(): Response
    {
        $adminUsersPortal = User::where('is_alma', 0)->get();
        $adminUsersAlma = User::where('is_alma', 1)->get();
        $slskeyGroups = SlskeyGroup::query()->get();

        return Inertia::render('AdminUsers/AdminUsersIndex', [
            'filters' => Request::all(),
            'adminUsersPortal' => AdminUserResource::collection($adminUsersPortal),
            'adminUsersAlma' => AdminUserResource::collection($adminUsersAlma),
            'slskeyGroups' => SlskeyGroupAdminResource::collection($slskeyGroups),
        ]);
    }

    /**
     * Create route for Admin Users
     *
     * @return Response
     */
    public function create(): Response
    {
        $availableSlskeyGroups = SlskeyGroup::all();

        return Inertia::render(
            'AdminUsers/AdminUsersCreate',
            [
                'availableSlskeyGroups' => SlskeyGroupSelectResource::collection($availableSlskeyGroups),
            ]
        );
    }

    /**
     * Show route for Admin Users
     *
     * @param string $userIdentifier
     * @return Response
     */
    public function show(string $userIdentifier): Response
    {
        $adminUser = User::query()
            ->where('user_identifier', $userIdentifier)
            ->firstOrFail();

        $availableSlskeyGroups = SlskeyGroup::all();

        return Inertia::render('AdminUsers/AdminUsersShow', [
            'adminUser' => AdminUserResource::make($adminUser),
            'availableSlskeyGroups' => SlskeyGroupSelectResource::collection($availableSlskeyGroups),
        ]);
    }

    /**
     * Store / Save route for Admin Users
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        // Validate request data
        $validatedData = Request::validate([
            'is_edu_id' => ['required', 'numeric'],
            'is_slsp_admin' => ['required', 'numeric'],
            'user_identifier' => ['required', 'max:255'],
            'display_name' => ['required', 'max:255'],
        ]);

        // Check if is_edu_id is 0
        if ($validatedData['is_edu_id'] === 1) {
            // Generate a random password
            $randomPassword = Str::random(10); // Generates a 10-character random string

            // Add password to validated data
            $validatedData['password'] = bcrypt($randomPassword); // Hash the password for security
        } else {
            Request::validate([
                'password' => ['required', 'min:8'],
            ]);
            $validatedData['password'] = bcrypt(Request::input('password'));
        }

        // Create user without generated password
        $user = User::create($validatedData);

        // check for permissions
        $this->managePermissions($user);

        // Redirect to index route
        return Redirect::route('admin.users.index')->with('success',  __('flashMessages.admin_user_created'));
    }

    /**
     * Update route for Admin Users
     *
     * @param string $userIdentifier
     * @return RedirectResponse
     */
    public function update(string $userIdentifier): RedirectResponse
    {
        $adminUser = User::query()
            ->where('user_identifier', $userIdentifier)
            ->firstOrFail();

        $adminUser->update(Request::validate([
            'is_edu_id' => ['required', 'numeric'],
            'is_slsp_admin' => ['required', 'numeric'],
            'user_identifier' => ['required', 'max:255'],
            'display_name' => ['required', 'max:255'],
        ]));

        $this->managePermissions($adminUser);

        return Redirect::route('admin.users.index')
            ->with('success', __('flashMessages.admin_user_updated'));
    }

    /**
     * Destroy route for Admin Users
     *
     * @param string $userIdentifier
     * @return RedirectResponse
     */
    public function destroy(string $userIdentifier): RedirectResponse
    {
        $adminUser = User::query()
            ->where('user_identifier', $userIdentifier)
            ->firstOrFail();

        $adminUser->delete();

        return Redirect::route('admin.users.index')
            ->with('success', __('flashMessages.admin_user_deleted'));
    }

    /**
     * Manage Permissions for Admin Users
     *
     * @param User $user
     * @return void
     */
    private function managePermissions(User $user): void
    {
        // Check for SLSP Admin
        $isSLSPAdmin = Request::input('is_slsp_admin');
        if ($isSLSPAdmin) {
            $user->setSLSPAdmin();
            $user->removeAllPermissions();
        } else {
            $user->removeSLSPAdmin();

            // Manage Permissions
            $oldGroups = $user->getSlskeyGroupPermissionsSlskeyCodes();
            $newGroups = collect(Request::input('slskeyGroups', []))->map(function ($group) {
                return $group['slskey_code'];
            })->toArray();
            $groupsToRemove = array_diff($oldGroups, $newGroups);
            $groupsToAdd = array_diff($newGroups, $oldGroups);
            foreach ($groupsToAdd as $slskeyCode) {
                $user->givePermissions($slskeyCode);
            }
            foreach ($groupsToRemove as $slskeyCode) {
                $user->removePermissions($slskeyCode);
            }
        }
    }

    /**
     * Reset Password for Admin Users
     *
     * @param string $userIdentifier
     * @return RedirectResponse
     */
    public function resetPassword(string $userIdentifier): RedirectResponse
    {
        $adminUser = User::query()
            ->where('user_identifier', $userIdentifier)
            ->firstOrFail();

        $newPassword = Request::input('password');

        $adminUser->resetPassword($newPassword);

        return Redirect::route('admin.users.index')
            ->with('success', __('flashMessages.admin_user_password_reset'));
    }
}

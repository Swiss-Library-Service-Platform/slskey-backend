<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class LandingController extends Controller
{
    /**
     * Index route for Landing
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Landing/LandingLoginEduID', []);
    }

    /**
     * Login route for Switch edu-ID
     *
     * @return HttpFoundationResponse
     */
    public function loginEduID(): HttpFoundationResponse
    {
        // Find EduID tenant ID
        $tenant = DB::table('saml2_tenants')
            ->select('uuid')
            ->where('key', '=', 'eduid')
            ->get()->first();

        if (!$tenant) {
            // return 404
            return new JsonResponse(['error' => 'Tenant not found'], 404);
        }

        // Redirect to Login route
        return Inertia::location('/saml2/' . $tenant->uuid . '/login?returnTo=' . route('activation.start'));
    }

    /**
     * Logout route for users with username and password
     * Edu-ID users are logged out via saml2 route, see HandleInertiaRequests.php
    */
    public function logoutUsernamePassword(): HttpFoundationResponse
    {
        $user = Auth::user();
        if (!$user) {
            return Redirect::route('loginform');
        }
        // Clear local sessions
        Session::flush();
        Auth::logout();

        return Redirect::route('loginform');
    }

    /**
     * Change initial password
     *
     * @return Response
     */
    public function changePassword(): Response
    {
        return Inertia::render('Auth/ChangeInitialPassword', []);
    }

    /**
     * Set new password
     *
     * @return RedirectResponse
     */
    public function setPassword(): RedirectResponse
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Validate password and check if password and password_confirmation match
        $password = request()->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->setPassword($password['password']);

        return Redirect::route('activation.start');
    }

    /**
     * No roles route
     *
     * @return Response
     */
    public function noroles(): Response
    {
        // Clear local sessions
        Auth::logout();
        Session::flush();

        return Inertia::render('Landing/LandingLoginEduID', [
            'flash.error' => __('flashMessages.errors.permissions_missing'),
        ]);
    }

    /**
     * Participate route
     *
     * @return Response
     */
    public function participate(): Response
    {
        return Inertia::render('Landing/ParticipateIndex', []);
    }
}

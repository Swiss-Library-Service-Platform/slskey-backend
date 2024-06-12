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
        return Inertia::render('Landing/LandingLoginIndex', []);
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
     * Logout route for Switch edu-ID
     *
     * FIXME: WORKAROUND 1:
     * This method is currently in a workaround state, because 24slides/laravel-saml2 does not work as intended
     * Usually one should call the edu-ID Logout first and wait for the SignedOut Event in Saml2EventProvider.php
     * but when this is done, and the User is loggedout and Session is flushed in SignedOut Event, somehow the User remains logged in
     * Workaround: We logout the User and Flush the Session here, so User is logout before calling the edu-ID Logout endpoint
     *
     * FIXME: WORKAROUND 2:
     * Also we currently need to send the nameID to he SAML2 Logout endpoint,
     * Usually the SAML2 Implementation should remember the nameID, but it does not
     *
     * @return void
     */
    public function logoutEduID(): HttpFoundationResponse
    {
        $user = Auth::user();
        $nameId = Session::get('nameId');

        // Clear local sessions // WORKAROUND 1, see above
        Auth::logout();
        Session::flush();

        // SAML2 Remote Logout (SWITCH edu-ID)
        if ($user->is_edu_id) {
            $tenant = DB::table('saml2_tenants')
                ->select('uuid')
                ->where('key', '=', 'eduid')
                ->get()->first();
            if (!$tenant) {
                // return 404
                return response()->json(['message' => 'Tenant not found'], 404);
            }

            return Redirect::route('saml.logout', [
                'uuid' => $tenant->uuid,
                'nameId' => $nameId, // WORKAROUND 2, see above
            ]);
        }

        return Redirect::route('login');
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

        return Inertia::render('Landing/LandingLoginIndex', [
            'flash.error' => 'You have no permissions. Please contact SLSP.',
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

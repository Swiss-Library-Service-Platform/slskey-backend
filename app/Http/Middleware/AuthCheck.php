<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // Check if the user has a password change date set

            if ((! Auth::user()->is_edu_id && Auth::user()->password_change_at == null)) {
                return redirect(route('login_changepassword'));
            }

            // Check if permissions
            if (Auth::user()->getSlskeyGroupsPermissionsIds()->isEmpty() && ! Auth::user()->isSLSPAdmin()) {
                return redirect(route('noroles'));
            }

            // The user is logged in, so allow access to the route
            return $next($request);
        }

        // The user is not logged in, redirect to the login page or take appropriate action
        return redirect()->guest('/login');
    }
}

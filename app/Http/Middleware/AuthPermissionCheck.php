<?php

namespace App\Http\Middleware;

use App\Models\SlskeyGroup;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthPermissionCheck
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
        $slspEmployee = Auth::user()->isSLSPAdmin();

        if ($slspEmployee) {
            // The user is logged in, so allow access to the route
            return $next($request);
        }

        // Check if institution has webhook workflow
        $slskeyCode = $request->input('slskey_code');
        if (! $slskeyCode) {
            abort(403);
        }

        // Get SlskeyGroup Details
        $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)->first();

        if (! $slskeyGroup) {
            abort(404);
        }

        $permissions = Auth::user()->getSlskeyGroupPermissionsSlskeyCodes();

        // change slskey to lower case
        $slskeyCode = strtolower($slskeyCode);
        // Check if user has permission to access this SLSKey Group
        if (! in_array($slskeyCode, $permissions)) {
            abort(403);
        }

        // Process request
        return $next($request);
    }
}

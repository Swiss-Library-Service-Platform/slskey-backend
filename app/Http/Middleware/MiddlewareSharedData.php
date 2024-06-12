<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class MiddlewareSharedData extends Middleware
{
    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        $isAdmin = false;

        if (Auth::check()) {
            /** @var \App\Models\User */
            $user = Auth::user();
            $isAdmin = $user->isSLSPAdmin();
        }

        // get help url from config
        $helpUrl = config('app.help_page');

        return array_merge(parent::share($request), [
            'slskeyadmin' => $isAdmin,
            'helpUrl' => $helpUrl,
        ]);
    }
}

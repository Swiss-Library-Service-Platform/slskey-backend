<?php

namespace App\Http\Middleware;

use App\Models\SlskeyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        $isAdmin = false;
        $numberOfPermittedSlskeyGroups = 0;

        if (Auth::check()) {
            /** @var \App\Models\User */
            $user = Auth::user();
            $isAdmin = $user->isSLSPAdmin();
            $numberOfGroupPermissions = $user->getNumberOfPermissions();
            $numberOfPermittedSlskeyGroups = $isAdmin ? SlskeyGroup::count() : $numberOfGroupPermissions;
        }

        // get help url from config
        $helpUrl = config('app.help_page');

        // eduID SAML tenantId
        $tenantId = DB::table('saml2_tenants')
                ->select('uuid')
                ->where('key', '=', 'eduid')
                ->get()->first()->uuid;

        return array_merge(parent::share($request), [
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                ];
            },
            'locale' => App::currentLocale(),
            'numberOfPermittedSlskeyGroups' => $numberOfPermittedSlskeyGroups,
            'isSlskeyAdmin' => $isAdmin,
            'helpUrl' => $helpUrl,
            'logoutUrl' => route('saml.logout', [
                'uuid' => $tenantId,
                'nameId' => Session::get('nameId')
            ]),
        ]);
    }
}

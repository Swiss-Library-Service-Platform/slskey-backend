<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class Saml2EventProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(\Slides\Saml2\Events\SignedIn::class, function (\Slides\Saml2\Events\SignedIn $event) {
            // your own code preventing reuse of a $messageId to stop replay attacks
            $samlUser = $event->getSaml2User();

            $uniqueID = $samlUser->getAttributesWithFriendlyName()['swissEduPersonUniqueID'][0];
            $name = $samlUser->getAttributesWithFriendlyName()['givenName'][0];

            // $user = // find user by ID or attribute
            $user = User::where('user_identifier', '=', $uniqueID)
                ->where('is_edu_id', '=', true)
                ->first();

            // Create user if not exists
            if (! $user) {
                DB::table('users')->insert([
                    'display_name' => $name,
                    'user_identifier' => $uniqueID,
                    'password' => bin2hex(random_bytes(8)), // random 16 char password
                    'password_change_at' => Carbon::now(),
                    'is_edu_id' => true,
                ]);
                $user = User::where('user_identifier', '=', $uniqueID)->first();
            } else {
                // Update user if exists
                DB::table('users')
                    ->where('user_identifier', $uniqueID)
                    ->update([
                        'display_name' => $name,
                    ]);
            }
            // Save nameId to session
            Session::put('nameId', $samlUser->getNameId());
            // Login user
            Auth::login($user);
            Session::save();
        });

        /*
        This was not working here:

        Event::listen(\Slides\Saml2\Events\SignedOut::class, function (\Slides\Saml2\Events\SignedOut $event) {
            Log::channel('saml')->info('Before logout: ', [Auth::user(), Session::all()]);
            Auth::logout();
            Session::flush();
            Session::regenerate();
            Log::channel('saml')->info('After logout: ', [Auth::user(), Session::all()]);
        });
        */
    }
}

<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('login', function (Request $request) {
            $user_identifier = (string) $request->user_identifier;

            return Limit::perMinute(5)->by($user_identifier.$request->ip());
        });

        // Make sure only User login via username&password, when user is not an edu_id
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('user_identifier', $request->user_identifier)->first();

            if ($user && ! $user->is_edu_id) {
                if (Auth::attempt($request->only('user_identifier', 'password'))) {
                    return $user;
                }
            }
        });
    }
}

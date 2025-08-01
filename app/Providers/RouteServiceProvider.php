<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Configure webhook-specific rate limiting
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(300) // 5 requests per second on average
                ->by($request->ip())
                ->response(function () {
                    // Usually 429 would be returned
                    // But we return 529, as Alma performs a retry for 5xx errors
                    return response('Too many webhook requests. Please wait before retrying.', 529);
                });
        });

        // Configure cloud app-specific rate limiting
        RateLimiter::for('cloudapp', function (Request $request) {
            return Limit::perMinute(60) // 1 request per second on average
                ->by($request->ip())
                ->response(function () {
                    return response('Too many cloud app requests. Please wait before retrying.', 429);
                });
        });
    }
}

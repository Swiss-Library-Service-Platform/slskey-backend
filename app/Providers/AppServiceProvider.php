<?php

namespace App\Providers;

use App\Interfaces\AlmaAPIInterface;
use App\Interfaces\Repositories\SlskeyActivationRepositoryInterface;
use App\Interfaces\SwitchAPIInterface;
use App\Repositories\SlskeyActivationRepository\SlskeyActivationRepository;
use App\Services\API\AlmaAPIService;
use App\Services\API\SwitchAPIService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL as FacadesURL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        // Bind Services for application
        // Note: (PestPHP) Tests use Mockery to switch out the actual services with mocks
        $this->app->bind(AlmaAPIInterface::class, function () {
            return new AlmaAPIService(
                config('services.alma.41SLSP_NETWORK.base_url'),
                config('services.alma.41SLSP_NETWORK.api_key')
            );
        });
        $this->app->bind(SwitchAPIInterface::class, function () {
            return new SwitchAPIService(
                config('services.switch.base_url'),
                config('services.switch.api_user'),
                config('services.switch.api_password'),
                config('services.switch.natlic_grop')
            );
        });
        $this->app->bind(SlskeyActivationRepositoryInterface::class, function () {
            return new SlskeyActivationRepository();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::enforceMorphMap([
            // 'library' => 'App\Models\Library',
            // 'contract_group' => 'App\Models\ContractGroup',
            // 'institution_zone' => 'App\Models\InstitutionZone',
            // 'financial_address' => 'App\Models\FinancialAddress',
        ]);
        Inertia::share('appEnv', config('app.env'));

        if (App::environment('production') || App::environment('test')) {
            FacadesURL::forceScheme('https');
        }
    }
}

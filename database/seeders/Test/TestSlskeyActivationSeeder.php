<?php

namespace Database\Seeders\Test;

use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\SlskeyUserService;
use Illuminate\Database\Seeder;

class TestSlskeyActivationSeeder extends Seeder
{
    public const TOTAL_NUMBERS_OF_USERS = 10;

    protected $slskeyUserService;

    public function __construct()
    {
        $this->slskeyUserService = app(SlskeyUserService::class);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
            SLSKey Users
        */

        $mockSwitchApiService = mockSwitchApiServiceActivation();

        for ($i = 0; $i < self::TOTAL_NUMBERS_OF_USERS; $i++) {
            $user = SlskeyUser::factory()->create();
            /*
             *   SLSKey Activations
             */
            foreach (SlskeyGroup::query()->get() as $slskeyGroup) {
                $typeOfActivation = rand(1, 30);
                // Get a random action date between now and 1 year ago
                $actionDate = now()->subDays(rand(1, 365));
                // copy value of actiondate without ref
                $mockSwitchApiService = mockSwitchApiServiceActivation($mockSwitchApiService);
                $this->slskeyUserService->activateSlskeyUser($user->primary_id, $slskeyGroup->slskey_code, null, 'Import Job', null, null);

                if ($typeOfActivation < 20) {
                    // do nothing
                } elseif ($typeOfActivation < 28) {
                    $this->slskeyUserService->deactivateSlskeyUser($user->primary_id, $slskeyGroup->slskey_code, null, null, 'Import Job', null, null, null, null);
                } else {
                    $this->slskeyUserService->blockSlskeyUser($user->primary_id, $slskeyGroup->slskey_code, null, null, 'Import Job', null, null, null, null);
                }
            }
        }
    }
}

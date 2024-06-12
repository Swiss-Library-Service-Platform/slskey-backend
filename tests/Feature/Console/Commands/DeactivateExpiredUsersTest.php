<?php

// Test if the command is registered

use App\Enums\WorkflowEnums;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\UserService;
use Carbon\Carbon;

it('is registered', function () {
    $this->assertTrue(class_exists(\App\Console\Commands\DeactivateExpiredUsers::class));
});

// Test if the command has the correct signature
it('has the correct signature', function () {
    $command = $this->app->make(\App\Console\Commands\DeactivateExpiredUsers::class);
    $this->assertEquals('job:deactivate-expired-users', $command->getSignature());
});

// Test without expired activations
it('does not deactivate any users if there are no expired activations', function () {
    $command = $this->app->make(\App\Console\Commands\DeactivateExpiredUsers::class);
    $response = $command->handle();
    $this->assertEquals(0, $response);
});

// Test with expired activations
it('deactivates an expired user', function () {
    $mockSwitchApiService = mockSwitchApiServiceActivation();

    // Seed SlskeyGroups
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $slskeyGroup = SlskeyGroup::query()->where('workflow', WorkflowEnums::MANUAL)->first();
    // Create User and activation
    $expiringUser = SlskeyUser::factory()->create();
    $activatedUser = SlskeyUser::factory()->create();
    $userService = app(UserService::class);
    $response = $userService->activateSlskeyUser($expiringUser->primary_id, $slskeyGroup->slskey_code, null, 'Import Job', null, null);#
    $response = $userService->activateSlskeyUser($activatedUser->primary_id, $slskeyGroup->slskey_code, null, 'Import Job', null, null);
    assertUserActivationActivated($expiringUser->primary_id, $slskeyGroup->slskey_code);
    assertUserActivationActivated($activatedUser->primary_id, $slskeyGroup->slskey_code);
    // Get Expiration Date and Travel in time past that date
    $expiringActivation = SlskeyActivation::query()->where('slskey_user_id', $expiringUser->id)->first();
    $expirationDate = $expiringActivation->expiration_date;
    $activeActivation = SlskeyActivation::query()->where('slskey_user_id', $activatedUser->id)->first();
    $activateActivationExpiration = new Carbon($activeActivation->expiration_date);
    $activeActivation->setExpirationDate($activateActivationExpiration->addDays(10));
    $this->travelTo($expirationDate);
    $this->travel(3)->day();

    // Call command
    $mockSwitchApiService = mockSwitchApiServiceDeactivation($mockSwitchApiService);
    $command = $this->app->make(\App\Console\Commands\DeactivateExpiredUsers::class);
    $response = $command->handle();
    $this->assertEquals(1, $response);
    assertUserActivationDeactivated($expiringUser->primary_id, $slskeyGroup->slskey_code);
    assertUserActivationActivated($activatedUser->primary_id, $slskeyGroup->slskey_code);
    // Call command again
    $response = $command->handle();
    $this->assertEquals(0, $response);
});

<?php

use App\DTO\AlmaServiceResponse;
use App\Enums\WorkflowEnums;
use App\Interfaces\AlmaAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

it('is registered', function () {
    $this->assertTrue(class_exists(\App\Console\Commands\RemindExpiringUsers::class));
});

// Test if the command has the correct signature
it('has the correct signature', function () {
    $command = $this->app->make(\App\Console\Commands\RemindExpiringUsers::class);
    $this->assertEquals('job:send-remind-expiring-users', $command->getSignature());
});

// Test without expiring activations
it('does not send any reminders if there are no expiring activations', function () {
    $command = $this->app->make(\App\Console\Commands\RemindExpiringUsers::class);
    $response = $command->handle();
    $this->assertEquals(0, $response);
});

// Test with expiring activations
it('sends reminders for expiring activations', function () {
    $mockSwitchApiService = mockSwitchApiServiceActivation();
    // Seed SlskeyGroups
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $slskeyGroup = SlskeyGroup::query()->where('workflow', WorkflowEnums::MANUAL)->first();
    // Create User and activation
    $remindedUser = SlskeyUser::factory()->create();
    $nonRemindedUser = SlskeyUser::factory()->create();
    $userService = app(UserService::class);
    $response = $userService->activateSlskeyUser($remindedUser->primary_id, $slskeyGroup->slskey_code, null, 'Import Job', null, null);#
    $response = $userService->activateSlskeyUser($nonRemindedUser->primary_id, $slskeyGroup->slskey_code, null, 'Import Job', null, null);
    assertUserActivationActivated($remindedUser->primary_id, $slskeyGroup->slskey_code);
    assertUserActivationActivated($nonRemindedUser->primary_id, $slskeyGroup->slskey_code);

    // Get Expiration Date and Travel in time past that date
    $reminderActivation = SlskeyActivation::query()->where('slskey_user_id', $remindedUser->id)->first();
    $nonRemindedActivation = SlskeyActivation::query()->where('slskey_user_id', $nonRemindedUser->id)->first();
    $activateActivationExpiration = new Carbon($nonRemindedActivation->expiration_date);
    $nonRemindedActivation->setExpirationDate($activateActivationExpiration->addDays(10));

    // Travel to date where user gets reminded
    $this->travelTo($reminderActivation->expiration_date);
    $this->travel(-$slskeyGroup->days_expiration_reminder)->days();

    // Prepare Alma Request
    $almaUser = AlmaUser::factory()->make(['primary_id' => $remindedUser->primary_id, 'preferred_language' => 'en']);
    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    // Mock mail service
    mockMailServiceRemindExpiringUsers();

    // Call command
    $command = $this->app->make(\App\Console\Commands\RemindExpiringUsers::class);
    $response = $command->handle();
    $this->assertEquals(1, $response);

    // Assert DB
    assertUserActivationActivated($remindedUser->primary_id, $slskeyGroup->slskey_code);
    assertUserActivationActivated($nonRemindedUser->primary_id, $slskeyGroup->slskey_code);
    assertUserRemindedHistory($remindedUser->primary_id, $slskeyGroup->slskey_code);
    assertUserNoRemindedHistory($nonRemindedUser->primary_id, $slskeyGroup->slskey_code);

    // Call command
    $command = $this->app->make(\App\Console\Commands\RemindExpiringUsers::class);
    $response = $command->handle();
    $this->assertEquals(0, $response);
});

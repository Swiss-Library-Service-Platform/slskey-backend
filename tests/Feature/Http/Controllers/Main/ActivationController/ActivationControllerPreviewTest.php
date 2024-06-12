<?php

use App\DTO\AlmaServiceResponse;
use App\Interfaces\AlmaAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyUser;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Inertia\Testing\AssertableInertia;

expect()->extend('sessionHasSuccessStartingWith', function (string $prefix) {
    /** @var TestResponse $response */
    $response = $this->value;

    $sessionData = $response->baseResponse->getSession()->get('success');

    $this->assertTrue(
        str_starts_with($sessionData, __($prefix)),
        "Failed asserting that the session has a 'success' message starting with '{$prefix}'."
    );

    return $this;
});

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails preview because - not found in alma', function () {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions('man1')->create();
    $this->actingAs($user);

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(false, 404, null, 'Alma Error 404'));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $identifier = 'identifier';
    $response = $this->get("activation/{$identifier}?origin=ACTIVATION_START");

    $response->assertStatus(302);
    $response->assertSessionHas('error', 'Alma Error 404');
    $response->assertLocation(route('activation.start'));
});

it('succeeds to preview - new slskeyuser - 1 group', function ($almaUser) {
    $slskeyCode = 'man1';
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($slskeyCode)->create();
    $this->actingAs($user);

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $identifier = 'identifier';
    $response = $this->get("activation/{$identifier}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Activation/ActivationPreview')
            ->where('identifier', $identifier)
            ->where('almaUser', $almaUser)
            ->where('slskeyUser', null)
            ->where('preselectedSlskeyCode', $slskeyCode)
            ->has(
                'slskeyGroups',
                1,
                fn (AssertableInertia $page) => $page
                    ->where('value', $slskeyCode)
                    ->etc()
            )
    );
})->with([
    'user found in alma' => fn () => AlmaUser::factory()->make(),
]);

it('suceeds to preview - new slskeyuser - 2 groups', function ($almaUser) {
    $user = User::factory()->non_edu_id_password_changed()->withPermissions('man1', 'man2')->create();
    $this->actingAs($user);

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $identifier = 'identifier';
    $response = $this->get("activation/{$identifier}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Activation/ActivationPreview')
            ->where('identifier', $identifier)
            ->where('almaUser', $almaUser)
            ->where('slskeyUser', null)
            ->where('preselectedSlskeyCode', null)
            // 2 slskeygroups with value man1 and man2
            ->has('slskeyGroups', 2)
    );
})->with([
    'user found in alma' => fn () => AlmaUser::factory()->make(),
]);

it('succeeds to preview - existing user - 2 groups', function ($almaUser) {
    seedSlskeyActivations();

    // get random existing user
    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();

    $user = User::factory()->non_edu_id_password_changed()->withPermissions('man1', 'man2')->create();
    $this->actingAs($user);

    $almaUser->primary_id = $slskeyUser->primary_id;
    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $identifier = $slskeyUser->primary_id;
    $response = $this->get("activation/{$identifier}");

    $response->assertStatus(200);

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Activation/ActivationPreview')
            ->where('identifier', $identifier)
            ->where('almaUser', $almaUser)
            ->has('slskeyUser')
            ->has('slskeyUser.data.slskey_activations', 2)
            ->where('preselectedSlskeyCode', null)
            // 2 slskeygroups with value man1 and man2
            ->has('slskeyGroups', 2)
    );
})->with([
    'user found in alma' => fn () => AlmaUser::factory()->make(),
]);

it('succeeds to preview - existing user - 2 groups - redirect to user detail', function ($almaUser) {
    seedSlskeyActivations();

    // get random existing user
    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();

    $user = User::factory()->non_edu_id_password_changed()->withPermissions('man1', 'man2')->create();
    $this->actingAs($user);

    $almaUser->primary_id = $slskeyUser->primary_id;
    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $identifier = $slskeyUser->primary_id;
    $queryParams = [
        'origin' => 'ACTIVATION_START',
    ];
    $url = "activation/{$identifier}?".http_build_query($queryParams);

    // Include the query parameters in the GET request by passing the URL
    $response = $this->get($url);

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', $slskeyUser->primary_id));
})->with([
    'user found in alma' => fn () => AlmaUser::factory()->make(),
]);

it('succeeds to preview - existing user for unauthorized group - 2 groups', function ($almaUser) {
    $slskeyCode = 'man1';
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($slskeyCode)->create();
    $this->actingAs($user);

    mockSwitchApiServiceActivation();

    $identifier = 'my_new_user@eduid.ch';
    $response = test()->post("activation/$identifier", [
        'slskey_code' => $slskeyCode,
        'alma_user' => $almaUser->toArray(),
    ]);

    $response->assertStatus(302);
    $response->assertLocation(route('users.show', ['identifier' => $identifier]));
    $response->assertSessionHas('success');
    expect($response)->toHaveSessionHasSuccessStartingWith('flashMessages.user_activated');

    // logout current sessions
    $this->post(route('logout'));

    // login as user with different slskeycode
    $slskeyCode = 'man2';
    $user = User::factory()->non_edu_id_password_changed()->withPermissions($slskeyCode)->create();
    $this->actingAs($user);

    $almaUser->primary_id = $identifier;
    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $queryParams = [
        'origin' => 'ACTIVATION_START',
    ];
    $url = "activation/{$identifier}?".http_build_query($queryParams);

    // Include the query parameters in the GET request by passing the URL
    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertInertia(fn ($assert) => $assert
        ->component('Activation/ActivationPreview')
        ->where('identifier', $identifier)
        ->where('almaUser', $almaUser)
        ->where('slskeyUser', null)
        ->where('preselectedSlskeyCode', $slskeyCode)
        // 2 slskeygroups with value man1 and man2
        ->has('slskeyGroups', 1));
})->with([
    'user found in alma' => fn () => AlmaUser::factory()->make(),
]);

<?php

use App\DTO\AlmaServiceResponse;
use App\Interfaces\AlmaAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyUser;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails because not loggedin', function () {
    $response = $this->get('/users');
    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

it('fails because not authorized', function () {
    $user = User::factory()->non_edu_id_password_changed()->create();
    $this->actingAs($user);
    $response = $this->get('/users');
    $response->assertStatus(302);
    $response->assertRedirect('/noroles');
});

it('succeeds to show users - 1 group - no users', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    $response = $this->get('/users');

    $response->assertStatus(200);

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 0)
            ->has('filters')
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 1)
    );
});

it('succeeds to show users - 2 groups - no users', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(2)->create();
    $this->actingAs($user);
    $response = $this->get('/users');

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 0)
            ->has('filters')
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 2)
    );
});

it('succeeds to show users - 1 group - with users', function () {
    seedSlskeyActivations();
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    $response = $this->get('/users');

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 10)
            ->where('slskeyUsers.meta.total', 10)
            ->has('filters')
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 1)
            ->where(
                'slskeyUsers.data.0.primary_id',
                function ($primaryId) {
                    return SlskeyUser::isPrimaryIdEduId($primaryId);
                }
            )
            ->has('slskeyUsers.data.0.slskey_activations', 1)
    );
});

it('succeeds to show users - 2 groups - with users', function () {
    seedSlskeyActivations();

    $user = User::factory()->edu_id()->withRandomPermissions(2)->create();
    $this->actingAs($user);
    $response = $this->get('/users');

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 10)
            ->where('slskeyUsers.meta.total', 10)
            ->has('filters')
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 2)
            // primary is ends with eduid.ch
            ->where(
                'slskeyUsers.data.0.primary_id',
                function ($primaryId) {
                    return SlskeyUser::isPrimaryIdEduId($primaryId);
                }
            )
            ->has('slskeyUsers.data.0.slskey_activations', 2)
    );
});

it('succeeds to search - no results', function () {
    seedSlskeyActivations();

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(false, 400, null, 'Not found'));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    $response = $this->get('/users?search=123456');

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 0)
            ->has('filters')
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 1)
    );
});

it('succeeds to search - with result', function () {
    seedSlskeyActivations();

    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();
    $identifier = $slskeyUser->primary_id;
    $response = $this->get("/users?search=$identifier");

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 1)
            ->has('filters')
            ->where('filters.search', $identifier)
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 1)
    );
});

it('succeeds to search - result from alma', function () {
    seedSlskeyActivations();

    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();
    $almaUser = AlmaUser::factory()->make(['primary_id' => $slskeyUser->primary_id]);

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $identifier = 'something that is not gonna be found';
    $response = $this->get("/users?search=$identifier");

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/UsersIndex')
            ->where('perPage', 10)
            ->has('slskeyUsers')
            ->has('slskeyUsers.data', 1)
            ->has('filters')
            ->where('filters.search', $identifier)
            ->has('slskeyGroups')
            ->has('slskeyGroups.data', 1)
    );
});

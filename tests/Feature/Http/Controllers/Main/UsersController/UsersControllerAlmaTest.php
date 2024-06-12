<?php

use App\DTO\AlmaServiceResponse;
use App\Interfaces\AlmaAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyUser;
use App\Models\User;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails alma because no slskey user', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $identifier = 'notexisting';
    $response = $this->get("/users/alma/$identifier");
    $response->assertStatus(404);
});

it('fails alma because no alma info', function () {
    seedSlskeyActivations();
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(false, 404, null, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $response = $this->get("/users/alma/{$slskeyUser->primary_id}");

    $response->assertStatus(200);
    $response->assertJson([
        'almaUser' => null,
    ]);
});

it('succeeds to get alma user info', function () {
    seedSlskeyActivations();

    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();
    $almaUser = AlmaUser::factory()->make(['primary_id' => $slskeyUser->primary_id]);

    $almaApiServiceMock = Mockery::mock(AlmaAPIInterface::class);
    $almaApiServiceMock->shouldReceive('getUserByIdentifier')->andReturn(new AlmaServiceResponse(true, 200, $almaUser, ''));
    App::instance(AlmaAPIInterface::class, $almaApiServiceMock);

    $response = $this->get("/users/alma/{$slskeyUser->primary_id}");

    $response->assertStatus(200);
    $response->assertJson([
        'almaUser' => $almaUser->toArray(),
    ]);
});

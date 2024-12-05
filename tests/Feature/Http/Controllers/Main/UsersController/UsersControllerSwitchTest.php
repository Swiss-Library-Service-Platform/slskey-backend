<?php

use App\Models\SlskeyUser;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails because no switch status', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $identifier = 'notexisting';
    $slskeyCode = 'notexisting';
    $response = $this->get("/users/switch/$identifier/$slskeyCode");
    $response->assertStatus(404);
});

it('succeeds to get switch status disabled', function () {
    seedSlskeyActivations();

    $user = User::factory()->edu_id()->withPermissions('man1')->create();
    $this->actingAs($user);

    mockSwitchApiServiceUserIsOnAllGroups(false);

    $identifier = SlskeyUser::query()
        ->filterWithPermittedActivations()
        ->first()->primary_id;
    $slskeyCode = 'man1';

    $response = $this->get("/users/switch/$identifier/$slskeyCode");
    $response->assertStatus(200);

    $response->assertJson([
        'status' => false,
    ]);
});

it('succeeds to get switch status enabled', function () {
    seedSlskeyActivations();

    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    mockSwitchApiServiceUserIsOnAllGroups(true);

    $identifier = SlskeyUser::query()
        ->filterWithPermittedActivations()
        ->first()->primary_id;
    $slskeyCode = 'man1';

    $response = $this->get("/users/switch/$identifier/$slskeyCode");
    $response->assertStatus(200);

    $response->assertJson([
        'status' => true,
    ]);
});

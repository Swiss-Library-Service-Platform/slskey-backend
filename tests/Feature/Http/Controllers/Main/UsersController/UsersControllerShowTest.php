<?php

use App\Models\SlskeyUser;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails because not loggedin', function () {
    $response = $this->get('/users');
    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

it('fails because no user found', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $identifier = 'notexisting';
    $response = $this->get("/users/$identifier");
    $response->assertStatus(404);
});

it('succeeds to show user - 1 group - 1 user', function () {
    seedSlskeyActivations();

    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $slskeyUser = SlskeyUser::query()
        ->withPermittedActivations()
        ->withPermittedHistories()
        ->first();
    $response = $this->get("/users/{$slskeyUser->primary_id}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Users/Detail/UserDetail')
            // check if slskeyuser is SlskeyUserDetailResource
            ->has(
                'slskeyUser.data',
                fn (AssertableInertia $page) => $page
                    ->where('id', $slskeyUser->id)
                    ->where('primary_id', $slskeyUser->primary_id)
                    ->where('full_name', $slskeyUser->first_name.' '.$slskeyUser->last_name)
                    ->has('slskey_activations')
                    ->has('slskey_histories')
            )
    );
});

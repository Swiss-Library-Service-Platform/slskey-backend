<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails activation start because no roles', function () {
    $user = User::factory()->non_edu_id_password_changed()->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $response->assertStatus(302);
    $response->assertLocation('/noroles');
});

it('succeeds to render start page', function () {
    $user = User::factory()->non_edu_id_password_changed()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('Activation/ActivationStart')
    );
});

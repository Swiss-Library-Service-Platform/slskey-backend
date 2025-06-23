<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

test('it renders the landing index', function () {
    $response = $this->get('/login');
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Landing/LandingLoginEduID');
    });
});

test('it redirects to error page when no edu-ID configured', function () {
    $response = $this->get('/login/eduid');
    $response->assertStatus(404);
});

test('it logs out a user and redirects to landing page', function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    $response = $this->get('/logout/user');
    Auth::shouldReceive('logout');
    $response->assertRedirect('/login');
});

test('logout fails for edu-ID if no edu-ID tenant is configured', function () {
    $user = User::factory()->edu_id()->create();
    $this->actingAs($user);
    $response = $this->get('/logout/eduid');
    Auth::shouldReceive('logout');
    $response->assertStatus(404);
});

test('it does redirect to no roles', function (User $user) {
    $this->actingAs($user);
    $response = $this->get('/');
    $response->assertStatus(302);
    $response->assertRedirect('/noroles');
})->with([
    'edu-id user' => fn () => User::factory()->edu_id()->create(),
    'non-edu-id user' => fn () => User::factory()->non_edu_id_password_changed()->create(),
]);

test('it renders no roles page', function () {
    $response = $this->get('/noroles');
    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Landing/LandingLoginEduID')
        ->has('flash.error')
    );

    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Landing/LandingLoginEduID');
        $page->has('flash.error');
        $page->where('flash.error', 'You have no permissions. Please contact SLSP.');
    });
});

test('it renders the participate page', function () {
    $response = $this->get('/participate');
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Landing/ParticipateIndex');
    });
});

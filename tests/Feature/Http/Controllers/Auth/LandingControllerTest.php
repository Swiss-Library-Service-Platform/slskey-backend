<?php

use App\Models\User;
use Database\Factories\Saml2TenantFactory;
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

test('it redirects to edu-ID Login page', function () {
    $tenant = Saml2TenantFactory::new()->eduid()->create();

    // Check Redirect
    $response = $this->get('/login/eduid');
    $response->assertStatus(302)
        ->assertRedirect("/saml2/{$tenant->uuid}/login?returnTo=".route('activation.start'));
});

test('it require to change the password', function () {
    $user = User::factory()->non_edu_id_password_unchanged()->create();
    $this->actingAs($user);
    $response = $this->get('/');
    $response->assertStatus(302);
    $response->assertLocation('/login/changepassword');
});

test('it renders the change password page', function () {
    $user = User::factory()->non_edu_id_password_unchanged()->create();
    $this->actingAs($user);
    $response = $this->get('/login/changepassword');
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Auth/ChangeInitialPassword');
    });
});

test('it requires the password and password_confirmation to match', function () {
    $user = User::factory()->non_edu_id_password_unchanged()->create();
    $this->actingAs($user);
    $response = $this->post('/login/changepassword', [
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword2',
    ]);
    $response->assertStatus(302);
    $response->assertSessionHasErrors('password');
    $this->assertAuthenticated();
});

test('it changes the password', function () {
    $user = User::factory()->non_edu_id_password_unchanged()->create();
    $this->actingAs($user);
    $response = $this->post('/login/changepassword', [
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);
    $response->assertStatus(302);
    $response->assertRedirect('/');
    $this->assertAuthenticated();
    $this->assertAuthenticatedAs($user);
    $this->assertTrue(Hash::check('newpassword', $user->password));
});

test('it redirects edu-ID user to edu-ID logout page', function () {
    $user = User::factory()->edu_id()->create();
    $tenant = Saml2TenantFactory::new()->eduid()->create();
    $this->actingAs($user);
    $response = $this->get('/logout/eduid');
    Auth::shouldReceive('logout');
    $response->assertRedirect('/saml2/'.$tenant->uuid.'/logout');
});

test('logout fails for edu-ID if no edu-ID tenant is configured', function () {
    $user = User::factory()->edu_id()->create();
    $this->actingAs($user);
    $response = $this->get('/logout/eduid');
    Auth::shouldReceive('logout');
    $response->assertStatus(404);
});

test('it logs out non edu-ID users and redirects to home page', function () {
    $user = User::factory()->non_edu_id_password_changed()->create();
    $this->actingAs($user);
    $response = $this->get('/logout/eduid');
    Auth::shouldReceive('logout');
    $response->assertRedirect('/login');
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

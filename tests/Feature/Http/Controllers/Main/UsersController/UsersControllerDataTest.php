<?php

use App\Models\SlskeyUser;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails to get user data because not logged in', function () {
    $response = $this->get('/users/data');
    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

it('fails to get user data because not authorized', function () {
    $user = User::factory()->non_edu_id_password_changed()->create();
    $this->actingAs($user);
    $response = $this->get('/users/data');
    $response->assertStatus(302);
    $response->assertRedirect('/noroles');
});

it('succeeds to get user data - empty result', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    $response = $this->get('/users/data');

    $response->assertStatus(200);
    $response->assertJson([
        'slskeyUsers' => [
            'data' => [],
        ]
    ]);
});

it('succeeds to get user data with pagination parameters', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    
    $response = $this->get('/users/data?perPage=25');
    $response->assertStatus(200);
    
    $responseData = $response->json();
    expect($responseData)->toHaveKey('slskeyUsers');
    expect($responseData['slskeyUsers'])->toHaveKey('data');
});

it('succeeds to get user data with filter parameters', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    
    $response = $this->get('/users/data?search=test&status=ACTIVE');
    $response->assertStatus(200);
    
    $responseData = $response->json();
    expect($responseData)->toHaveKey('slskeyUsers');
    expect($responseData['slskeyUsers'])->toHaveKey('data');
});
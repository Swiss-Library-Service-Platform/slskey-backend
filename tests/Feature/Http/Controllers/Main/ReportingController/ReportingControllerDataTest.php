<?php

use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails to get reporting data because not logged in', function () {
    $response = $this->get('/reporting/data');
    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

it('fails to get reporting data because not authorized', function () {
    $user = User::factory()->edu_id()->create();
    $this->actingAs($user);
    $response = $this->get('/reporting/data');
    $response->assertStatus(302);
    $response->assertRedirect('/noroles');
});

it('succeeds to get reporting data - no activations', function () {
    $user = User::factory()->edu_id()->withPermissions('man1')->create();
    $this->actingAs($user);
    
    $response = $this->get('/reporting/data');
    $response->assertStatus(200);
    
    $responseData = $response->json();
    expect($responseData)->toHaveKey('reportCounts');
    expect($responseData)->toHaveKey('isAnyEducationalUsers');
    expect($responseData['isAnyEducationalUsers'])->toBeFalse();
    
    // Should have at least current month data
    expect($responseData['reportCounts'])->toHaveCount(1);
    expect($responseData['reportCounts'][0])->toHaveKey('total_active_users');
    expect($responseData['reportCounts'][0]['total_active_users'])->toBe(0);
});

it('succeeds to get reporting data with slskeyCode filter', function () {
    $user = User::factory()->edu_id()->withPermissions('man1')->create();
    $this->actingAs($user);
    
    $response = $this->get('/reporting/data?slskeyCode=man1');
    $response->assertStatus(200);
    
    $responseData = $response->json();
    expect($responseData)->toHaveKey('reportCounts');
    expect($responseData)->toHaveKey('isAnyEducationalUsers');
});
<?php

use App\Models\SlskeyGroup;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails reporting settings because not authorized', function () {
    $user = User::factory()->edu_id()->create();
    $this->actingAs($user);

    $response = $this->get('/reporting/man1');
    $response->assertStatus(302);
    $response->assertLocation('/noroles');
});

it('fails reporting settings because slskey group not found', function () {
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);

    $response = $this->get('/reporting/notexisting');

    $response->assertStatus(403);
});

it('fails reporting settings because slskey group not authorized', function () {
    $user = User::factory()->edu_id()->withPermissions('man1')->create();
    $this->actingAs($user);

    $response = $this->get('/reporting/man2');

    $response->assertStatus(403);
});

it('succeeds to show reporting settings', function () {
    $slskeyCode = 'man1';
    $user = User::factory()->edu_id()->withPermissions($slskeyCode)->create();
    $this->actingAs($user);

    $response = $this->get("/reporting/$slskeyCode");

    $slskeyGroup = SlskeyGroup::query()
        ->where('slskey_code', $slskeyCode)
        ->first();
    $response->assertStatus(200);

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Reporting/ReportingSettings')
            ->where('slskeyGroup.data', [
                'name' => $slskeyGroup->name,
                'slskey_code' => $slskeyGroup->slskey_code,
                'emails' => [],
            ])
    );
});

it('succeeds to add and remove reporting mail', function () {
    $slskeyCode = 'man1';
    $user = User::factory()->edu_id()->withPermissions($slskeyCode)->create();
    $this->actingAs($user);

    $response = $this->post("/reporting/$slskeyCode", [
        'email' => 'test@web.de',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success', __('flashMessages.reportmail_created'));

    $response = $this->delete("/reporting/$slskeyCode/1");

    $response->assertStatus(302);
    $response->assertSessionHas('success', __('flashMessages.reportmail_deleted'));
});

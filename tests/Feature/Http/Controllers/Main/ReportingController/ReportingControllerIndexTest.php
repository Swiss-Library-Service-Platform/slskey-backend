<?php

use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Models\SlskeyReportCounts;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails reporting because not authorized', function () {
    $user = User::factory()->edu_id()->create();
    $this->actingAs($user);

    $response = $this->get('/reporting');
    $response->assertStatus(302);
    $response->assertLocation('/noroles');
});

it('succeeds to render reporting - no activations', function () {
    $user = User::factory()->edu_id()->withPermissions('man1')->create();
    $this->actingAs($user);

    $response = $this->get('/reporting');
    $response->assertStatus(200);

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Reporting/ReportingIndex')
            ->where('selectedSlskeyCode', null)
            ->has('slskeyGroups')
            ->has(
                'slskeyGroups.data',
                1,
                fn ($assert) => $assert
                    ->where('slskey_code', 'man1')
                    ->etc()
            )
    );
});

it('succeeds reporting - activations', function () {
    seedSlskeyActivations();

    $slskeyCodes = ['man1'];
    $slskeyGroupIds = SlskeyGroup::query()
        ->whereIn('slskey_code', $slskeyCodes)
        ->pluck('id')
        ->toArray();
    $user = User::factory()->edu_id()->withPermissions($slskeyCodes)->create();
    $this->actingAs($user);

    $response = $this->get('/reporting');
    $response->assertStatus(200);

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Reporting/ReportingIndex')
            ->where('selectedSlskeyCode', null)
            ->has('slskeyGroups')
            ->has(
                'slskeyGroups.data',
                1,
                fn ($assert) => $assert
                    ->where('slskey_code', 'man1')
                    ->etc()
            )
    );
});

it('fails reporting - selected slskey group not authorized', function () {
    $user = User::factory()->edu_id()->withPermissions('man1')->create();
    $this->actingAs($user);

    $response = $this->get('/reporting?slskeyCode=man2');
    $response->assertStatus(403);
});

it('succeeds reporting - activation - selected slskey group', function () {
    seedSlskeyActivations();

    $slskeyCodes = ['man1', 'man2'];
    $slskeyGroupIds = SlskeyGroup::query()
        ->whereIn('slskey_code', $slskeyCodes)
        ->pluck('id')
        ->toArray();
    $selectedSlskeyGroup = $slskeyCodes[0];
    $user = User::factory()->edu_id()->withPermissions($slskeyCodes)->create();
    $this->actingAs($user);

    $response = $this->get('/reporting?slskeyCode=man1');
    $response->assertStatus(200);

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Reporting/ReportingIndex')
            ->where('selectedSlskeyCode', $selectedSlskeyGroup)
            ->has('slskeyGroups')
            ->has(
                'slskeyGroups.data',
                2,
                fn ($assert) => $assert
                    ->where('slskey_code', 'man1')
                    ->etc()
            )
    );
});

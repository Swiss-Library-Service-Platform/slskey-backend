<?php

use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Models\SlskeyHistoryMonth;
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
            ->where('selectedSlskeyGroup', null)
            ->has(
                'slskeyHistories',
                1,
                fn ($assert) => $assert
                    ->where('total_users', 0)
                    ->where('month', now()->format('m'))
                    ->where('year', now()->format('Y'))
                    ->etc()
            )
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

    // Get SlskeyActivations
    $firstHistory = SlskeyHistory::query()
        ->whereIn('slskey_group_id', $slskeyGroupIds)
        ->orderBy('created_at', 'asc')
        ->first();
    $firstDate = $firstHistory ? $firstHistory->created_at : date('Y-m-d');
    $slskeyHistories = SlskeyHistoryMonth::getGroupedByMonthWithActionCounts($slskeyGroupIds, $firstDate);

    expect($slskeyHistories)->toBeGreaterThan(1);
    $numberOfMonths = count($slskeyHistories);
    $totalActivations = $slskeyHistories[0]->total_users;

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Reporting/ReportingIndex')
            ->where('selectedSlskeyGroup', null)
            ->has(
                'slskeyHistories',
                $numberOfMonths,
                fn ($assert) => $assert
                    ->where('month', now()->format('m'))
                    ->where('year', now()->format('Y'))
                    ->where('total_users', $totalActivations)
                    ->etc()
            )
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

    // Get SlskeyActivations
    $selectedSlskeyGroupIds = [SlskeyGroup::query()
        ->where('slskey_code', $selectedSlskeyGroup)
        ->firstOrFail()
        ->id];
    $firstHistory = SlskeyHistory::query()
        ->whereIn('slskey_group_id', $selectedSlskeyGroupIds)
        ->orderBy('created_at', 'asc')
        ->first();
    $firstDate = $firstHistory ? $firstHistory->created_at : date('Y-m-d');
    $slskeyHistories = SlskeyHistoryMonth::getGroupedByMonthWithActionCounts($selectedSlskeyGroupIds, $firstDate);

    expect($slskeyHistories)->toBeGreaterThan(1);
    $numberOfMonths = count($slskeyHistories);
    $totalActivations = $slskeyHistories[0]->total_users;

    $response->assertInertia(
        fn ($assert) => $assert
            ->component('Reporting/ReportingIndex')
            ->where('selectedSlskeyGroup', $selectedSlskeyGroup)
            ->has(
                'slskeyHistories',
                $numberOfMonths,
                fn ($assert) => $assert
                    ->where('month', now()->format('m'))
                    ->where('year', now()->format('Y'))
                    ->where('total_users', $totalActivations)
                    ->etc()
            )
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

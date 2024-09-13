<?php

use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('succeeds to find 1 group, with no user activations', function () {
    $almaUsername = 'Admin';
    $slskeyCode = 'man1';
    $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)->first();
    $almaInstCode = $slskeyGroup->alma_iz;
    $username = "$almaInstCode-$almaUsername";

    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);

    $identifier = '123@eduid.ch';

    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->getJson("/api/v1/cloudapp/user/$identifier/activate");

    $response->assertStatus(200);
    $response->assertJson([[
        'name' => $slskeyGroup->name,
        'value' => $slskeyGroup->slskey_code,
        'workflow' => $slskeyGroup->workflow,
        'activation' => null,
    ]]);
});

it('succeeds to find 1 group, with 1 user activations', function () {
    seedSlskeyActivations();

    $almaUsername = 'Admin';
    $slskeyCode = 'man1';
    $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)->first();
    $almaInstCode = $slskeyGroup->alma_iz;
    $username = "$almaInstCode-$almaUsername";

    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);

    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();
    $identifier = $slskeyUser->primary_id;
    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->getJson("/api/v1/cloudapp/user/$identifier/activate");

    $response->assertStatus(200);
    $response->assertJsonCount(1);
    $response->assertJson([[
        'name' => $slskeyGroup->name,
        'value' => $slskeyGroup->slskey_code,
        'workflow' => $slskeyGroup->workflow,
    ]]);
    $response->assertJson([[
        'activation' => [
            'slskey_user_id' => $slskeyUser->id,
            'slskey_group_id' => $slskeyGroup->id,
            'remark' => null,
            'slskey_group' => [
                'name' => $slskeyGroup->name,
                'slskey_code' => $slskeyGroup->slskey_code,
                'workflow' => $slskeyGroup->workflow,
            ],
        ],
    ]]);
});

it('succeeds to find 2 groups, with 1 user activations', function () {
    seedSlskeyActivations();

    $almaUsername = 'Admin';
    $slskeyCodes = ['man1', 'man2'];
    $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCodes[0])->first();
    $almaInstCode = $slskeyGroup->alma_iz;
    $username = "$almaInstCode-$almaUsername";

    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCodes)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);

    $slskeyUser = SlskeyUser::query()->inRandomOrder()->first();
    $identifier = $slskeyUser->primary_id;
    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->getJson("/api/v1/cloudapp/user/$identifier/activate");

    $response->assertStatus(200);
    $response->assertJsonCount(2);
    $response->assertJson([[
        'name' => $slskeyGroup->name,
        'value' => $slskeyGroup->slskey_code,
        'workflow' => $slskeyGroup->workflow,
    ]]);
    $response->assertJson([[
        'activation' => [
            'slskey_user_id' => $slskeyUser->id,
            'slskey_group_id' => $slskeyGroup->id,
            'remark' => null,
            'slskey_group' => [
                'name' => $slskeyGroup->name,
                'slskey_code' => $slskeyGroup->slskey_code,
                'workflow' => $slskeyGroup->workflow,
            ],
        ],
    ]]);
});

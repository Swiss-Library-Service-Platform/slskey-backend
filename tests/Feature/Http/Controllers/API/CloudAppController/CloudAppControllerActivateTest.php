<?php

use App\Http\Middleware\AuthCloudApp;
use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails the activation, because no slskey code given', function () {
    $almaUsername = 'Admin';
    $almaInstCode = '12345';
    $slskeyCode = 'man1';

    $username = "$almaInstCode-$almaUsername";
    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);
    $identifier = '123';

    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->postJson("/api/v1/cloudapp/user/$identifier/activate", [
        // 'slskey_code' => $slskeyCode,
        // 'remark' => 'test'
    ]);

    $response->assertStatus(422);
    $response->assertSeeText('The slskey code field is required.');
});

it('fails the activation, because no alma user given', function () {
    $almaUsername = 'Admin';
    $almaInstCode = '12345';
    $slskeyCode = 'man1';
    $username = "$almaInstCode-$almaUsername";

    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);
    $identifier = '123';

    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->postJson("/api/v1/cloudapp/user/$identifier/activate", [
        'slskey_code' => $slskeyCode,
        'remark' => 'test',
        //'alma_user' => AlmaUser::factory()->make()
    ]);

    $response->assertStatus(422);
    $response->assertSeeText('The alma user field is required.');
});

it('fails the activation, because primary is is not an edu id', function () {
    $almaUsername = 'Admin';
    $almaInstCode = '12345';
    $slskeyCode = 'man1';
    $username = "$almaInstCode-$almaUsername";

    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);
    $identifier = '123';

    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->postJson("/api/v1/cloudapp/user/$identifier/activate", [
        'slskey_code' => $slskeyCode,
        'remark' => 'test',
        'alma_user' => getAlmaUserData($identifier),
    ]);

    $response->assertStatus(400);
    $response->assertSeeText('Error: '.__('flashMessages.errors.activations.no_edu_id'));
});

it('succeeds the activation & extension', function () {
    $almaUsername = 'Admin';
    $almaInstCode = '12345';
    $slskeyCode = 'man1';
    $username = "$almaInstCode-$almaUsername";

    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);

    mockSwitchApiServiceActivation();

    $identifier = '123@eduid.ch';

    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->postJson("/api/v1/cloudapp/user/$identifier/activate", [
        'slskey_code' => $slskeyCode,
        'remark' => 'test',
        'alma_user' => getAlmaUserData($identifier),
    ]);

    $response->assertStatus(200);
    $response->assertSeeText(__('flashMessages.user_activated'));
    assertUserActivationActivated($identifier, $slskeyCode);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->postJson("/api/v1/cloudapp/user/$identifier/activate", [
        'slskey_code' => $slskeyCode,
        'remark' => 'test',
        'alma_user' => getAlmaUserData($identifier),
    ]);

    $response->assertStatus(200);
    $response->assertSeeText(__('flashMessages.user_extended'));
    assertUserActivationActivated($identifier, $slskeyCode);
});

/*
    Helper functions
*/

function getMockedAuthCloudApp($almaUsername, $almaInstCode)
{
    test()->mock(AuthCloudApp::class, function ($mock) use ($almaUsername, $almaInstCode) {
        $mock->shouldAllowMockingProtectedMethods();
        $mock->makePartial();
        $mock->shouldReceive('extractTokenFromRequest')->andReturn('token');
        $mock->shouldReceive('decodeJWTToken')->andReturn((object) ['sub' => $almaUsername, 'inst_code' => $almaInstCode]);
    });
}

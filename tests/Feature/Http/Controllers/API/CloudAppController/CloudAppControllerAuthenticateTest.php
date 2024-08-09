<?php

use App\Http\Middleware\AuthCloudApp;
use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use App\Models\User;

const EXPIRED_TOKEN = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImV4bGhlcC0wMSJ9.eyJpc3MiOiJodHRwczovL2FwcHMwMS5leHQuZXhsaWJyaXNncm91cC5jb20vYXV0aCIsImF1ZCI6IkV4bENsb3VkQXBwOiF-c2xza2V5LWNsb3VkLWFwcCIsInN1YiI6ImFkbWluIiwiaW5zdF9jb2RlIjoiNDFTTFNQX0hQSCIsImV4cCI6MTcxMjIyOTkzN30.V7-y15lCXDxvrUHtW3-OPHgy24nKw7iubUVqk_5cf5MGjHWS3RBCrz74FV-tyH2XypuW6n4LEaXfH1S26iEB95cuh63uqf_nLTeEjxl5_kFxAQ9erY_TsfOVVyJbl6EA4KaVvFVSel7nn5AzvelfX0N6VqoZT41EI6fnPF6703bbRdfI1e0H54cbRU6lzV_KlsHtqfLVd3-mmzcg3Jkacct7gcQ9CkICjzHZOrQMzCuiFtfeRbQjwCIQQqqMKEDC53VHKE2ElBbLxhl9XVjG0lEKxgpV5HXlSV3zp3LJHDVl-DSlJvx4BAPpW1YMwR5lk3tv0Whcdga927bKfgxZwA';
const INVALID_TOKEN = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImV4bGhlcC0wMSJ9.eyJpc3MiOiJodHRwczovL2FwcHMwMS5leHQuZXhsaWJyaXNncm91cC5jb20vYXV0aCIsImF1ZCI6IkV4bENsb3VkQXBwOiF-c2xza2V5LWNsb3VkLWFwcCIsInN1YiI6ImludmFsaWRfdXNlciIsImV4cCI6MTcxMjIyOTkzN30.V7-y15lCXDxvrUHtW3-OPHgy24nKw7iubUVqk_5cf5MGjHWS3RBCrz74FV-tyH2XypuW6n4LEaXfH1S26iEB95cuh63uqf_nLTeEjxl5_kFxAQ9erY_TsfOVVyJbl6EA4KaVvFVSel7nn5AzvelfX0N6VqoZT41EI6fnPF6703bbRdfI1e0H54cbRU6lzV_KlsHtqfLVd3-mmzcg3Jkacct7gcQ9CkICjzHZOrQMzCuiFtfeRbQjwCIQQqqMKEDC53VHKE2ElBbLxhl9XVjG0lEKxgpV5HXlSV3zp3LJHDVl-DSlJvx4BAPpW1YMwR5lk3tv0Whcdga927bKfgxZwA';

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

it('fails auth, because no auth token given', function () {
    $response = $this->getJson('/api/v1/cloudapp/authenticate');
    $response->assertStatus(401);
    $response->assertSeeText('Authorization failed. Token not provided.');
});

it('succeeds to authenticate user', function ($almaUser) {
    $almaUsername = 'Admin';
    $slskeyCode = 'man1';
    $slskeyGroup = SlskeyGroup::where('slskey_code', $slskeyCode)->first();
    $almaInstCode = $slskeyGroup->alma_iz;
    $username = "$almaInstCode-$almaUsername";
    $user = User::factory(['user_identifier' => $username])->edu_id()->withPermissions($slskeyCode)->create();

    getMockedAuthCloudApp($almaUsername, $almaInstCode);

    // call authenticate
    $response = $this->withHeaders([
        'Authorization' => 'Bearer 123', // does not matter, we mock the decoding
    ])->getJson("/api/v1/cloudapp/authenticate");

    assertAdminUserExisting($username);
})->with([
    'user found in alma' => fn () => AlmaUser::factory()->make(),
]);

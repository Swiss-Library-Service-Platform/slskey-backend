<?php

use App\Enums\WebhookResponseEnums;
use App\Http\Middleware\AuthWebhooks;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
    $this->slskeyCode = 'webhook2';
    $this->almaInst = '41SLSP_2';
});

it('fails auth, because no inst given', function () {
    $response = $this->post("/api/v1/webhooks/$this->slskeyCode");
    $response->assertStatus(422);
    $response->assertSeeText(WebhookResponseEnums::ERROR_NO_INSTITUTION);
});

it('fails auth, because no slskey group', function () {
    $response = $this->post('/api/v1/webhooks/invalid', ['institution' => ['value' => 'invalid']]);
    $response->assertStatus(422);
    $response->assertSeeText(WebhookResponseEnums::ERROR_NO_SLSKEY_GROUP);
});

it('fails auth, because invalid secret', function () {
    mockWebhookAuth(false);

    $response = $this->post("/api/v1/webhooks/$this->slskeyCode", ['institution' => ['value' => $this->almaInst]]);

    $response->assertStatus(422);
    $response->assertSeeText(WebhookResponseEnums::ERROR_INVALID_SECRET);
});

it('succeeds auth, but throws validation error', function () {
    mockWebhookAuth(true);

    $response = $this->post("/api/v1/webhooks/$this->slskeyCode", ['institution' => ['value' => $this->almaInst]]);
    $response->assertStatus(422);
});

/*
    Helper functions
*/
function mockWebhookAuth($success)
{
    test()->mock(AuthWebhooks::class, function ($mock) use ($success) {
        $mock->shouldAllowMockingProtectedMethods();
        $mock->makePartial();
        $mock->shouldReceive('isHashValid')->andReturn($success);
    });
}

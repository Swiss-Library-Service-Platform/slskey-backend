<?php

it('succeeds to respond empty challenge', function () {
    $response = $this->getJson('/api/v1/webhooks/challenge');
    $response->assertStatus(200);
    $response->assertJson(['challenge' => null]);
});

it('succeeds to respond given challenge', function () {
    $queryParams = [
        'challenge' => 'test',
    ];
    $url = ('/api/v1/webhooks/challenge?'.http_build_query($queryParams));
    $response = $this->getJson($url);
    $response->assertStatus(200);
    $response->assertJson(['challenge' => 'test']);
});

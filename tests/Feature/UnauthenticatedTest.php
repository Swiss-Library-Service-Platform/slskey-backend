<?php

test('it redirects to landing page when not logged in', function () {
    $response = $this->get('/');
    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

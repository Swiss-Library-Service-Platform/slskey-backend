<?php

use Illuminate\Testing\TestResponse;

expect()->extend('toHaveSessionHasSuccessStartingWith', function (string $prefix) {
    /** @var TestResponse $response */
    $response = $this->value;

    $sessionData = $response->baseResponse->getSession()->get('success');

    PHPUnit\Framework\Assert::assertTrue(
        str_starts_with($sessionData, __($prefix)),
        "Failed asserting that the session has a 'success' message starting with '{$prefix}'."
    );

    return $this;
});

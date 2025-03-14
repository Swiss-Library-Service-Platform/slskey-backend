<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('app.env') !== 'testing') {
            exit("❌ Tests should not be run in production! Exiting...\n");
        }
    }
}

<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Integration');
uses(RefreshDatabase::class)->in('Feature', 'Integration');

// load Helpers
require_once __DIR__.'/Helpers/DBAsserts.php';
require_once __DIR__.'/Helpers/SwitchServiceMock.php';
require_once __DIR__.'/Helpers/WebhooksUserData.php';
require_once __DIR__.'/Helpers/AlmaUserData.php';
require_once __DIR__.'/Helpers/CallTestSeeder.php';
require_once __DIR__.'/Helpers/MailServiceMock.php';
require_once __DIR__.'/Helpers/CustomAsserts.php';

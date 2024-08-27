<?php

namespace App\Logging\DatabaseLogger;

use Monolog\Logger;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @return Logger
     */
    public function __invoke(array $config)
    {
        return new Logger('db_log_default', [
            new DatabaseLogHandler(),
        ]);
    }
}

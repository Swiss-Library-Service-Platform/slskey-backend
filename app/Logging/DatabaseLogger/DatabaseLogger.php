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
        $table = $config['table'];
        return new Logger($table, [
            new DatabaseLogHandler(),
        ]);
    }
}

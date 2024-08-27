<?php

namespace App\Logging\DatabaseLogger;

use Exception;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Throwable;
use App\Models\LogDefault;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    const DEFAULT_CHANNEL = 'log_default';

    /**
     * @inheritDoc
     */
    protected function write($record): void
    {
        $record = is_array($record) ? $record : $record->toArray();

        $exception = $record['context']['exception'] ?? null;

        if ($exception instanceof Throwable) {
            $record['context']['exception'] = (string) $exception;
        }

        try {
            $this->createLogDefault($record);
        } catch (Exception $e) {
            $fallbackChannels = config('logging.channels.fallback.channels', ['single']);

            Log::stack($fallbackChannels)->debug($record['formatted'] ?? $record['message']);

            Log::stack($fallbackChannels)->debug('Could not log to the database.', [
                'exception' => $e,
            ]);
        }
    }

    protected function createLogDefault(array $record): void
    {
        LogDefault::create([
            'level' => $record['level'],
            'level_name' => $record['level_name'],
            'message' => $record['message'],
            'logged_at' => $record['datetime'],
            'context' => $record['context'],
            'extra' => $record['extra'],
        ]);
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataImportProgressEvent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $currentRow;

    public $primary_id;

    public $slskey_code;

    public $success;

    public $message;

    public $isActive;

    public $isVerified;

    /**
     * Create a new event instance.
     *
     * @param integer $currentRow
     * @param string $primary_id
     * @param string $slskey_code
     * @param boolean $success
     * @param string $message
     * @param boolean $isActive
     */
    public function __construct(int $currentRow, string $primary_id, string $slskey_code, bool $success, string $message, ?bool $isActive, ?bool $isVerified)
    {
        $this->currentRow = $currentRow;
        $this->primary_id = $primary_id;
        $this->slskey_code = $slskey_code;
        $this->success = $success;
        $this->message = $message;
        $this->isActive = $isActive;
        $this->isVerified = $isVerified;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('import-progress');
    }

    /**
     * Get the broadcast name
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'import-progress-row';
    }
}

<?php

namespace App\DTO;

class SlskeyUserServiceResponse
{
    /**
     * Indicates if the request was successful.
     *
     * @var boolean
     */
    public bool $success;

    /**
     * The message if the request was not successful.
     *
     * @var string|null
     */
    public ?string $message;

    /**
     * Create a new instance.
     *
     * @param boolean $success
     * @param string|null $message
     */
    public function __construct(bool $success, ?string $message)
    {
        $this->success = $success;
        $this->message = $message;
    }
}

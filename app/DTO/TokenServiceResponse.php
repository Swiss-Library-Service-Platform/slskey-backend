<?php

namespace App\DTO;

class TokenServiceResponse
{
    /**
     * Indicates if the request was successful.
     *
     * @var boolean
     */
    public bool $success;

    /**
     * The token if the request was successful.
     *
     * @var string|null
     */
    public ?string $token;

    /**
     * The reactivation link if the request was successful.
     *
     * @var string|null
     */
    public ?string $reactivationLink;

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
     * @param string|null $token
     * @param string|null $reactivationLink
     * @param string|null $message
     */
    public function __construct(bool $success, ?string $token, ?string $reactivationLink, ?string $message)
    {
        $this->success = $success;
        $this->token = $token;
        $this->reactivationLink = $reactivationLink;
        $this->message = $message;
    }
}

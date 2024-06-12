<?php

namespace App\DTO;

use App\Models\AlmaUser;

class AlmaServiceResponse
{
    /**
     * Indicates if the request was successful.
     *
     * @var boolean
     */
    public bool $success;

    /**
     * The status code of the response.
     *
     * @var integer
     */
    public int $statusCode;

    /**
     * The Alma user object.
     *
     * @var AlmaUser|null
     */
    public ?AlmaUser $almaUser;

    /**
     * The error text if the request was not successful.
     *
     * @var string|null
     */
    public ?string $errorText;

    /**
     * Create a new instance.
     *
     * @param boolean $success
     * @param integer $statusCode
     * @param AlmaUser|null $almaUser
     * @param string|null $errorText
     */
    public function __construct(bool $success, int $statusCode, ?AlmaUser $almaUser, ?string $errorText = null)
    {
        $this->success = $success;
        $this->statusCode = $statusCode;
        $this->almaUser = $almaUser;
        $this->errorText = $errorText;
    }
}

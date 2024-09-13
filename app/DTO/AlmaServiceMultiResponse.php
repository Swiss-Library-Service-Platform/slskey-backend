<?php

namespace App\DTO;

use App\Models\AlmaUser;

class AlmaServiceMultiResponse
{
    /**
     * Indicates if the request was successful.
     *
     * @var boolean
     */
    public bool $success;

    /**
     * The Alma user object.
     *
     * @var AlmaUser|null
     */
    public ?array $almaUsers;

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
     * @param Array<AlmaUser>|null $almaUsers
     * @param string|null $errorText
     */
    public function __construct(bool $success, ?array $almaUsers, ?string $errorText = null)
    {
        $this->success = $success;
        $this->almaUsers = $almaUsers;
        $this->errorText = $errorText;
    }
}

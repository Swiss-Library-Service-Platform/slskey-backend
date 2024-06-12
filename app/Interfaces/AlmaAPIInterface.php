<?php

namespace App\Interfaces;

use App\DTO\AlmaServiceResponse;

interface AlmaAPIInterface
{
    public function getUserByIdentifier(string $identifier): AlmaServiceResponse;
}

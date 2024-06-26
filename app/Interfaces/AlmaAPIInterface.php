<?php

namespace App\Interfaces;

use App\DTO\AlmaServiceResponse;

interface AlmaAPIInterface
{
    public function getApiKey(): string;
    public function setApiKey(string $apiKey): void;
    public function getUserByIdentifier(string $identifier): AlmaServiceResponse;
}

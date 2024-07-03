<?php

namespace App\Interfaces;

use App\DTO\AlmaServiceMultiResponse;
use App\DTO\AlmaServiceSingleResponse;

interface AlmaAPIInterface
{
    public function getApiKey(): string;
    public function setApiKey(string $izCode, string $apiKey): void;
    public function getUserFromSingleIz(string $identifier, string $izCode): AlmaServiceSingleResponse;
    public function getUserFromMultipleIzs(string $identifier, array $izCodes): AlmaServiceMultiResponse;
}

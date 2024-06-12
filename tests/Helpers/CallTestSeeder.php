<?php

function seedSlskeyActivations()
{
    $mockSwitchApiService = mockSwitchApiServiceActivation();
    test()->seed('Database\Seeders\Test\TestSlskeyActivationSeeder');
}

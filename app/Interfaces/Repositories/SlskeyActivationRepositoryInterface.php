<?php

namespace App\Interfaces\Repositories;

use App\Models\SlskeyGroup;

interface SlskeyActivationRepositoryInterface
{
    public function getActivationsToBeReminded(SlskeyGroup $slskeyGroup);
}

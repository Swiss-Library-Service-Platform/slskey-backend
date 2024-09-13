<?php

namespace App\Repositories\SlskeyActivationRepository;

use App\Interfaces\Repositories\SlskeyActivationRepositoryInterface;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use Illuminate\Database\Eloquent\Collection;

class SlskeyActivationRepository implements SlskeyActivationRepositoryInterface
{
    /**
     * Get all activations that need to be reminded
     *
     * @param SlskeyGroup $slskeyGroup
     * @return Collection
     */
    public function getActivationsToBeReminded(SlskeyGroup $slskeyGroup): Collection
    {
        $activationsToBeReminded = SlskeyActivation::query()
                ->where('slskey_group_id', $slskeyGroup->id)
                ->whereNotNull('expiration_date')
                ->where('reminded', false)
                ->where('expiration_date', '<', now()->addDays($slskeyGroup->days_expiration_reminder)->endOfDay())
                ->withSlskeyUserAndSlskeyGroup()
                ->get();

        return $activationsToBeReminded;
    }
}

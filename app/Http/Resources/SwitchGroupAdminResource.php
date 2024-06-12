<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SwitchGroupAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'switch_group_id' => $this->switch_group_id,
            'publishers' => $this->getPublisherArrayFromPublisherString(),
            // SlskeyGroups
            'slskeyGroups' => $this->slskeyGroups,
            'slskey_groups_count' => $this->slskeyGroups->count(),
        ];
    }
}

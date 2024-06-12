<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SwitchGroupAdminDetailResource extends JsonResource
{
    protected $switchApiService;

    public function __construct($resource, $switchApiService)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->switchApiService = $switchApiService;
    }

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
            'publishers' => $this->publishers,
            // SlskeyGroups
            'slskeyGroups' => $this->slskeyGroups,
            // Member Count
            'members_count' => $this->membersCount($this->switchApiService),
        ];
    }
}

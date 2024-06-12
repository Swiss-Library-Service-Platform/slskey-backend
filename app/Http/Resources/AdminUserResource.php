<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
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
            //'id' => $this->id,
            'user_identifier' => $this->user_identifier,
            'display_name' => $this->display_name,
            'is_edu_id' => $this->is_edu_id,
            'alma_username' => $this->alma_username,
            'alma_institution' => $this->alma_institution,
            'is_slsp_admin' => $this->isSLSPAdmin() ? 1 : 0,
            'slskeyGroups' => SlskeyGroupSelectResource::collection($this->getSlskeyGroupsPermissions()),
            // for dropdown:
            'name' => $this->user_identifier,
            'value' => $this->user_identifier,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublisherInternalResource extends JsonResource
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
            'entity_id' => $this->entity_id,
            'protocol' => $this->protocol,
            'internal_note' => $this->internal_note,
            'status' => $this->status,
            'switchGroups' => $this->switchGroups,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SlskeyUserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $fullName = $this->first_name && $this->last_name ? $this->first_name.' '.$this->last_name : null;

        return [
            'id' => $this->id,
            'primary_id' => $this->primary_id,
            'full_name' => $fullName,
            'slskey_activations' => $this->slskeyActivations,
            'slskey_histories' => $this->slskeyHistories->sortByDesc('created_at')->values()->all(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class SlskeyHistoryResource extends JsonResource
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
            'slskey_user_id' => $this->slskey_user_id,
            'slskey_group_id' => $this->slskey_group_id,
            'action' => $this->action,
            'author' => $this->author,
            'trigger' => $this->trigger,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'slskey_user' => $this->slskeyUser,
            'slskey_group' => $this->slskeyGroup,
            'author_display' => User::where('user_identifier', $this->author)->first()?->display_name,
        ];
    }
}

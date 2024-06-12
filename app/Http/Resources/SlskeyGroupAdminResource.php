<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SlskeyGroupAdminResource extends JsonResource
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
            'slskey_code' => $this->slskey_code,
            'name' => $this->name,
            'workflow' => $this->workflow,
            'send_activation_mail' => $this->send_activation_mail,
            // Manual
            'days_activation_duration' => $this->days_activation_duration,
            'days_expiration_reminder' => $this->days_expiration_reminder,
            // Webhook
            'alma_iz' => $this->alma_iz,
            'webhook_custom_verifier' => $this->webhook_custom_verifier,
            'webhook_secret' => $this->webhook_secret,
            // Switch Group Count
            'switchGroupsCount' => $this->switchGroups->count(),
            // Active Users
            'activeUserCount' => $this->activeUserCount(),
            // Publishers
            'publishers' => $this->getPublisherArray(),

            // for dropdown:
            'value' => $this->slskey_code,
        ];
    }
}

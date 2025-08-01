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
            'show_member_educational_institution' => $this->show_member_educational_institution,
            'mail_sender_address' => $this->mail_sender_address,
            // Manual
            'days_activation_duration' => $this->days_activation_duration,
            'days_expiration_reminder' => $this->days_expiration_reminder,
            // Webhook
            'alma_iz' => $this->alma_iz,
            'webhook_custom_verifier_activation' => $this->webhook_custom_verifier_activation,
            'webhook_custom_verifier_class' => $this->webhook_custom_verifier_class,
            'webhook_custom_verifier_deactivation' => $this->webhook_custom_verifier_deactivation,
            'mail_token_reactivation' => $this->mail_token_reactivation,
            'webhook_secret' => $this->webhook_secret,
            'webhook_persistent' => $this->webhook_persistent,
            'webhook_mail_activation' => $this->webhook_mail_activation,
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

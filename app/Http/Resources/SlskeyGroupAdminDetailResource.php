<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SlskeyGroupAdminDetailResource extends JsonResource
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
            // Webhook Email Activation
            'webhook_mail_activation' => $this->webhook_mail_activation,
            'webhook_mail_activation_domains' => $this->webhook_mail_activation_domains,
            'mail_token_reactivation_days_send_before_expiry' => $this->mail_token_reactivation_days_send_before_expiry,
            'mail_token_reactivation_days_token_validity' => $this->mail_token_reactivation_days_token_validity,
            // Cloud App Permissions
            'cloud_app_allow' => $this->cloud_app_allow,
            'cloud_app_roles' => $this->cloud_app_roles,
            'cloud_app_roles_scopes' => $this->cloud_app_roles_scopes,
            // SwitchGroups
            'switchGroups' => $this->switchGroups,
        ];
    }
}

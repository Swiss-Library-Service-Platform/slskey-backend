<?php

namespace App\Helpers\WebhookMailActivation;

use App\Models\AlmaUser;

/**
 * Class WebhookMailActivationHelper
 *
 * This controller is responsible for handling the verification of Z01 MBA users
 */
class WebhookMailActivationHelper
{
    // The domain list file
    private $domainListFile;

    /**
     * WebhookMailActivationHelper constructor.
     * @param string $domainListFileName The name of the domain list file
     */
    public function __construct($domainListFileName)
    {
        // get file content from path
        $domainList = file_get_contents(storage_path('app/email_domain_lists/'.$domainListFileName));
        $domainList = explode("\n", $domainList);
        $domainList = array_map('trim', $domainList);
        $domainList = array_filter($domainList, function ($domain) {
            return ! empty($domain);
        });
        $this->domainListFile = $domainList;
    }

    /**
     * Handles the verification process for email based activation.
     *
     * @param  AlmaUser  $userData  The user data containing the user identifier.
     * @return string Returns the new email address if the user is a member of the domain list, otherwise null.
     */
    public function getWebhookActivationMail(AlmaUser $almaUser): ?string
    {
        $newMBAEmailAddress = null;
        foreach ($almaUser->email as $emailObject) {
            $email_address = $emailObject->email_address;
            if (static::getIsEmailMemberOfDomainList($email_address)) {
                $newMBAEmailAddress = $email_address;
            }
        }

        return $newMBAEmailAddress;
    }

    /**
     * Checks if the given email address is a member of the domain list
     *
     * @param string $email
     * @return boolean
     */
    public function getIsEmailMemberOfDomainList(string $email): bool
    {
        try {
            $emailDomain = explode('@', $email)[1];
        } catch (\Exception $e) {
            return false;
        }

        return in_array($emailDomain, $this->domainListFile);
    }
}

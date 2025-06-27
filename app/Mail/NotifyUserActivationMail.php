<?php

namespace App\Mail;

use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyUserActivationMail extends Mailable
{
    use SerializesModels;

    public $slskeyGroup;
    public $almaUser;

    /**
     * Email subjects for different languages.
     */
    public const EMAIL_SUBJECTS = [
        'de' => 'Ihre SLSKey Freischaltung – :group',
        'en' => 'Your SLSKey activation – :group',
        'fr' => 'Votre activation SLSKey – :group',
        'it' => 'La sua attivazione SLSKey – :group',
    ];

    /**
     * Create a new message instance.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     */
    public function __construct(SlskeyGroup $slskeyGroup, AlmaUser $almaUser)
    {
        $this->slskeyGroup = $slskeyGroup;
        $this->almaUser = $almaUser;
    }

    /**
     * Build the message.
     *
     * @return NotifyUserActivationMail
     */
    public function build(): NotifyUserActivationMail
    {
        $language = $this->almaUser->preferred_language;
        $groupName = $this->slskeyGroup->name;
        $template = self::EMAIL_SUBJECTS[$language];
        $subject = str_replace(':group', $groupName, $template);

        $fromAddress = $this->slskeyGroup->mail_sender_address ?? config('mail.from.address');
        $fromName = 'SLSKey - ' . $groupName;

        return $this->subject($subject)
            ->from($fromAddress, $fromName)
            ->view('emails.activation.' . $this->slskeyGroup->slskey_code . '.' . $language . '.email', [
                'almaUser' => $this->almaUser,
            ]);
    }
}

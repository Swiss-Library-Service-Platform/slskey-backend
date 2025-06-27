<?php

namespace App\Mail;

use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RemindExpiringUserMail extends Mailable
{
    use SerializesModels;

    public $slskeyGroup;

    public $almaUser;

    public const EMAIL_SUBJECTS = [
        'de' => 'Ihre SLSKey Freischaltung läuft ab – :group',
        'en' => 'Your SLSKey activation is expiring – :group',
        'fr' => 'Votre activation SLSKey expire – :group',
        'it' => 'La sua attivazione SLSKey sta per scadere – :group',
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
     * @return RemindExpiringUserMail
     */
    public function build(): RemindExpiringUserMail
    {
        // Find preferred language
        $language = $this->almaUser->preferred_language;

        // Get group name
        $groupName = $this->slskeyGroup->name ?: '';

        // Get subject
        $template = self::EMAIL_SUBJECTS[$language];
        $subject = str_replace(':group', $groupName, $template);

        // From Sender
        $fromAddress = $this->slskeyGroup->mail_sender_address ?? config('mail.from.address');
        $fromName = 'SLSKey - ' . ($groupName ?? config('mail.from.name'));

        // Mail erzeugen
        return $this->subject($subject)
            ->from($fromAddress, $fromName)
            ->view('emails.remind.' . $this->slskeyGroup->slskey_code . '.' . $language . '.email', [
                'slskeyUser' => $this->almaUser,
            ]);
    }
}

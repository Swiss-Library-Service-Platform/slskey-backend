<?php

namespace App\Mail;

use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyUserActivationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $slskeyGroup;
    public $almaUser;

    /**
     * Email subjects for different languages.
     */
    public const EMAIL_SUBJECTS = [
        'de' => 'Ihre SLSKey Freischaltung',
        'en' => 'Your SLSKey activation',
        'fr' => 'Votre accès SLSKey',
        'it' => 'Il suo accesso SLSKey',
    ];

    /**
     * Create a new message instance.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param string $preferredLanguage
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
        // Get subject
        $slskeyCode = $this->slskeyGroup->slskey_code;
        $subject = self::EMAIL_SUBJECTS[$this->almaUser->preferred_language];

        // Return mail object
        return $this->subject($subject)->view('emails.activation.'.$slskeyCode.'.'.$this->almaUser->preferred_language.'.email', [
            'almaUser' => $this->almaUser
        ]);
    }
}

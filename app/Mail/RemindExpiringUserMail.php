<?php

namespace App\Mail;

use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RemindExpiringUserMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $slskeyGroup;

    public $almaUser;

    public const EMAIL_SUBJECTS = [
        // Zentralbibliothek Zürich
        'de' => 'Ihr SLSKey Account läuft ab',
        'en' => 'Your SLSKey account is expiring',
        'fr' => 'Votre accès SLSKey expire',
        'it' => 'Il suo accesso SLSKey sta per scadere',
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

        // Get subject
        $slskeyCode = $this->slskeyGroup->slskey_code;
        $subject = self::EMAIL_SUBJECTS[$language];

        // Return mail object
        return $this->subject($subject)->view('emails.remind.'.$slskeyCode.'.'.$language.'.email', [
            'slskeyUser' => $this->almaUser,
        ]);
    }
}

<?php

namespace App\Mail;

use App\Models\SlskeyGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReactivationTokenUserMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $slskeyGroup;

    public $reactivationLink;

    public const EMAIL_SUBJECTS = 'Ihr SLSKey Account läuft ab';

    /**
     * Create a new message instance.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param string $reactivationLink
     */
    public function __construct(SlskeyGroup $slskeyGroup, string $reactivationLink)
    {
        $this->slskeyGroup = $slskeyGroup;
        $this->reactivationLink = $reactivationLink;
    }

    /**
     * Build the message.
     *
     * @return ReactivationTokenUserMail
     */
    public function build(): ReactivationTokenUserMail
    {
        // Get subject
        $slskeyCode = $this->slskeyGroup->slskey_code;
        $subject = self::EMAIL_SUBJECTS;

        // From Sender
        $fromAddress = $this->slskeyGroup->mail_sender_address ?? config('mail.from.address');
        $fromName = 'SLSKey - ' . $this->slskeyGroup->name ?? config('mail.from.name');

        // Return mail object
        return $this->subject($subject)
            ->from($fromAddress, $fromName)
            ->view('emails.token.'.$slskeyCode.'.email', [
                'reactivationLink' => $this->reactivationLink,
        ]);
    }
}

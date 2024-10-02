<?php

namespace App\Services;

use App\Mail\MonthlyReportMail;
use App\Mail\NotifyUserActivationMail;
use App\Mail\ReactivationTokenUserMail;
use App\Mail\RemindExpiringUserMail;
use App\Models\AlmaUser;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistoryMonth;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;
use App\Models\SlskeyActivation;
use Illuminate\Mail\Mailable;

class MailService
{
    public $TEST_RECIPIENT;
    public $TEST_MODE;

    /**
     * MailService constructor.
     */
    public function __construct()
    {
        $this->TEST_RECIPIENT = config('app.testenv_mail_recipient');
        $this->TEST_MODE = config('app.env') !== 'production';
    }

    /**
     * Send Notify User Activation Mail.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @param SlskeyActivation $activation
     * @return ?SentMessage
     */
    public function sendNotifyUserActivationMail(SlskeyGroup $slskeyGroup, AlmaUser $almaUser, SlskeyActivation $activation): ?SentMessage
    {
        $mailObject = new NotifyUserActivationMail($slskeyGroup, $almaUser);
        $toMails = [ $activation->webhookActivationMail ?? $almaUser->preferred_email ];

        return $this->sendMail($mailObject, $toMails);
    }

    /**
     * Send Reactivation Token User Mail.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param string $webhookActivationMail
     * @param string $reactivationLink
     * @return ?SentMessage
     */
    public function sendReactivationTokenUserMail(SlskeyGroup $slskeyGroup, string $webhookActivationMail, string $reactivationLink): ?SentMessage
    {
        $mailObject = new ReactivationTokenUserMail($slskeyGroup, $reactivationLink);
        $toMails = [ $webhookActivationMail ];

        return $this->sendMail($mailObject, $toMails);
    }

    /**
     * Send Remind Expiring User Mail.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @return ?SentMessage
     */
    public function sendRemindExpiringUserMail(SlskeyGroup $slskeyGroup, AlmaUser $almaUser): ?SentMessage
    {
        $mailObject = new RemindExpiringUserMail($slskeyGroup, $almaUser);
        $toMails = [ $almaUser->preferred_email ];

        return $this->sendMail($mailObject, $toMails);
    }

    /**
     * Send Monthly Report Mail.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param SlskeyHistoryMonth $slskeyHistory
     * @param integer $totalCount
     * @param array $reportEmailAddresses
     * @return ?SentMessage
     */
    public function sendMonthlyReportMail(SlskeyGroup $slskeyGroup, SlskeyHistoryMonth $slskeyHistoryMonth, int $totalCurrentCount, int $totalCurrentMemberEducationalInstitutionCount, array $reportEmailAddresses): ?SentMessage
    {
        $mailObject = new MonthlyReportMail($slskeyGroup, $slskeyHistoryMonth, $totalCurrentCount, $totalCurrentMemberEducationalInstitutionCount);
        $toMails = $reportEmailAddresses;

        return $this->sendMail($mailObject, $toMails);
    }

    /**
     * Send Mail.
     *
     * @param Mailable $mailObject
     * @param array $toMails
     * @return ?SentMessage
     */
    public function sendMail(Mailable $mailObject, array $toMails): ?SentMessage
    {
        $toMails = $this->TEST_MODE ? [ $this->TEST_RECIPIENT ] : $toMails;

        return Mail::to($toMails)->send($mailObject);
    }
}

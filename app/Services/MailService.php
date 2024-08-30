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

class MailService
{
    public const TEST_RECIPIENT = 'sascha.villing@slsp.ch';
    public const TEST_MODE = true; // FIXME: set to false on prod, but true on dev and testing

    /**
     * Send Notify User Activation Mail.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param AlmaUser $almaUser
     * @param string|null $webhookActivationMail
     * @return ?SentMessage
     */
    public function sendNotifyUserActivationMail(SlskeyGroup $slskeyGroup, AlmaUser $almaUser, ?string $webhookActivationMail = null): ?SentMessage
    {
        $mailObject = new NotifyUserActivationMail($slskeyGroup, $almaUser);
        $toMail = $webhookActivationMail ?? $almaUser->preferred_email;
        $toMail = self::TEST_MODE ? self::TEST_RECIPIENT : $toMail;

        return Mail::to($toMail)->send($mailObject);
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
        $toMail = $webhookActivationMail;
        $toMail = self::TEST_MODE ? self::TEST_RECIPIENT : $toMail;

        return Mail::to($toMail)->send($mailObject);
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
        $toMail = $almaUser->preferred_email;
        $toMail = self::TEST_MODE ? self::TEST_RECIPIENT : $toMail;

        return Mail::to($toMail)->send($mailObject);
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
        $toMails = self::TEST_MODE ? self::TEST_RECIPIENT : $toMails;

        return Mail::to($toMails)->send($mailObject);
    }
}

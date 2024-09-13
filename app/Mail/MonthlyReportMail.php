<?php

namespace App\Mail;

use App\Models\SlskeyGroup;
use App\Models\SlskeyHistoryMonth;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $slskeyGroup;

    public $slskeyHistoryMonth;

    public $totalCurrentCount;

    public $totalCurrentMemberEducationalInstitutionCount;

    /**
     * Create a new message instance.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param SlskeyHistoryMonth $slskeyHistory
     * @param integer $totalCurrentCount
     */
    public function __construct(SlskeyGroup $slskeyGroup, SlskeyHistoryMonth $slskeyHistoryMonth, int $totalCurrentCount, int $totalCurrentMemberEducationalInstitutionCount)
    {
        $this->slskeyGroup = $slskeyGroup;
        $this->slskeyHistoryMonth = $slskeyHistoryMonth;
        $this->totalCurrentCount = $totalCurrentCount;
        $this->totalCurrentMemberEducationalInstitutionCount = $totalCurrentMemberEducationalInstitutionCount;
    }

    /**
     * Build the message.
     *
     * @return MonthlyReportMail
     */
    public function build(): MonthlyReportMail
    {
        // Get subject
        $subject = 'Monthly Report for '.$this->slskeyGroup->name;

        // Return mail object
        return $this->subject($subject)->view('emails.report.monthlyreport', [
            'slskeyHistory' => $this->slskeyHistoryMonth,
            'slskeyGroup' => $this->slskeyGroup,
            'totalCurrentCount' => $this->totalCurrentCount,
            'totalCurrentMemberEducationalInstitutionCount' => $this->totalCurrentMemberEducationalInstitutionCount,
        ]);
    }
}

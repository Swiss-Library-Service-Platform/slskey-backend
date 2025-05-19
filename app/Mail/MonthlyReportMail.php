<?php

namespace App\Mail;

use App\Models\SlskeyGroup;
use App\Models\SlskeyReportCounts;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use SerializesModels;

    public $slskeyGroup;

    public $slskeyReportCount;

    public $totalCurrentCount;

    public $totalCurrentMemberEducationalInstitutionCount;

    /**
     * Create a new message instance.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param SlskeyReportCounts $slskeyReportCount
     * @param integer $totalCurrentCount
     */
    public function __construct(SlskeyGroup $slskeyGroup, SlskeyReportCounts $slskeyReportCount, int $totalCurrentCount, int $totalCurrentMemberEducationalInstitutionCount)
    {
        $this->slskeyGroup = $slskeyGroup;
        $this->slskeyReportCount = $slskeyReportCount;
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
            'slskeyHistory' => $this->slskeyReportCount,
            'slskeyGroup' => $this->slskeyGroup,
            'totalCurrentCount' => $this->totalCurrentCount,
            'totalCurrentMemberEducationalInstitutionCount' => $this->totalCurrentMemberEducationalInstitutionCount,
        ]);
    }
}

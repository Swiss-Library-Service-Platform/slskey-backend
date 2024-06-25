<?php

namespace App\Mail;

use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Models\SlskeyHistoryMonth;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $slskeyGroup;

    public $slskeyHistory;

    public $activeCount;

    /**
     * Create a new message instance.
     *
     * @param SlskeyGroup $slskeyGroup
     * @param SlskeyHistoryMonth $slskeyHistory
     * @param integer $activeCount
     */
    public function __construct(SlskeyGroup $slskeyGroup, SlskeyHistoryMonth $slskeyHistory, int $activeCount)
    {
        $this->slskeyGroup = $slskeyGroup;
        $this->slskeyHistory = $slskeyHistory;
        $this->activeCount = $activeCount;
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
            'slskeyHistory' => $this->slskeyHistory,
            'slskeyGroup' => $this->slskeyGroup,
            'activeCount' => $this->activeCount,
        ]);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyFamilyMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $patientName;
    public $dischargeDate;
    public $familyName; // the name we’re emailing

    /**
     * Create a new message instance.
     */
public function __construct(Referral $referral, $familyName, $note = null)
{
    $this->referral = $referral;
    $this->familyName = $familyName;
    $this->note = $note;
}

public function build()
{
    return $this->subject('Patient Discharge Notice')
                ->view('emails.family-discharge')
                ->with([
                    'referral' => $this->referral,
                    'name'     => $this->familyName,
                    'note'     => $this->note,
                ]);
}

}

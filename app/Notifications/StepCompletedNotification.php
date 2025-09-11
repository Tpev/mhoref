<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StepCompletedNotification extends Notification
{
    use Queueable;

    protected $referralId;
    protected $stepId;
    protected $message;

    public function __construct($referralId, $stepId, $message)
    {
        $this->referralId = $referralId;
        $this->stepId = $stepId;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'referral_id' => $this->referralId,
            'step_id' => $this->stepId,
            'message' => $this->message,
        ];
    }
}

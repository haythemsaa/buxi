<?php

namespace App\Events;

use App\Models\PaymentReminder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PaymentReminder $reminder;

    /**
     * Create a new event instance.
     */
    public function __construct(PaymentReminder $reminder)
    {
        $this->reminder = $reminder;
    }
}

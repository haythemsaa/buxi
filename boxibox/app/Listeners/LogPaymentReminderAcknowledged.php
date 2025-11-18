<?php

namespace App\Listeners;

use App\Events\PaymentReminderAcknowledged;
use Illuminate\Support\Facades\Log;

class LogPaymentReminderAcknowledged
{
    /**
     * Handle the event.
     */
    public function handle(PaymentReminderAcknowledged $event): void
    {
        $reminder = $event->reminder;

        Log::info('Payment reminder acknowledged by customer', [
            'reminder_id' => $reminder->id,
            'invoice_id' => $reminder->invoice_id,
            'invoice_number' => $reminder->invoice->invoice_number,
            'customer_id' => $reminder->customer_id,
            'customer_email' => $reminder->customer->email,
            'phase' => $reminder->phase,
            'acknowledged_at' => $reminder->acknowledged_at,
        ]);
    }
}

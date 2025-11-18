<?php

namespace App\Listeners;

use App\Events\PaymentReminderSent;
use Illuminate\Support\Facades\Log;

class LogPaymentReminderSent
{
    /**
     * Handle the event.
     */
    public function handle(PaymentReminderSent $event): void
    {
        $reminder = $event->reminder;

        Log::info('Payment reminder sent', [
            'reminder_id' => $reminder->id,
            'invoice_id' => $reminder->invoice_id,
            'invoice_number' => $reminder->invoice->invoice_number,
            'customer_id' => $reminder->customer_id,
            'customer_email' => $reminder->customer->email,
            'phase' => $reminder->phase,
            'phase_name' => $reminder->getPhaseName(),
            'amount_due' => $reminder->amount_due,
            'late_fee' => $reminder->late_fee,
            'days_overdue' => $reminder->days_overdue,
            'sent_at' => $reminder->sent_at,
        ]);
    }
}

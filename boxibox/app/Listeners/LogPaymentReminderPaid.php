<?php

namespace App\Listeners;

use App\Events\PaymentReminderPaid;
use Illuminate\Support\Facades\Log;

class LogPaymentReminderPaid
{
    /**
     * Handle the event.
     */
    public function handle(PaymentReminderPaid $event): void
    {
        $reminder = $event->reminder;

        Log::info('Payment reminder marked as paid', [
            'reminder_id' => $reminder->id,
            'invoice_id' => $reminder->invoice_id,
            'invoice_number' => $reminder->invoice->invoice_number,
            'customer_id' => $reminder->customer_id,
            'phase' => $reminder->phase,
            'amount_due' => $reminder->amount_due,
            'late_fee' => $reminder->late_fee,
            'paid_at' => $reminder->paid_at,
        ]);
    }
}

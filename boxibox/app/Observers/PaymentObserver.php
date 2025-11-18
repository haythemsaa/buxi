<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\PaymentReminderService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    protected PaymentReminderService $reminderService;

    public function __construct(PaymentReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Quand un paiement est créé, vérifier si la facture est payée
        $invoice = $payment->invoice;

        if ($invoice && $invoice->isPaid()) {
            // Marquer tous les rappels de cette facture comme payés
            $this->reminderService->markInvoiceRemindersAsPaid($invoice);

            Log::info('Payment reminders marked as paid', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Vérifier le statut après mise à jour
        if ($payment->status === 'succeeded') {
            $invoice = $payment->invoice;

            if ($invoice && $invoice->isPaid()) {
                $this->reminderService->markInvoiceRemindersAsPaid($invoice);
            }
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        // Si un paiement est supprimé et que la facture n'est plus payée
        // On pourrait recréer des rappels si nécessaire
        // Mais généralement on ne supprime pas les paiements
    }
}

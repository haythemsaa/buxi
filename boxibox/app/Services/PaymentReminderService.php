<?php

namespace App\Services;

use App\Events\PaymentReminderPaid;
use App\Events\PaymentReminderSent;
use App\Models\Invoice;
use App\Models\PaymentReminder;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentReminderService
{
    /**
     * Traiter les rappels de paiement
     */
    public function processReminders(): array
    {
        $results = [
            'processed' => 0,
            'sent' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];

        // Récupérer toutes les factures impayées
        $overdueInvoices = $this->getOverdueInvoices();

        foreach ($overdueInvoices as $invoice) {
            try {
                $result = $this->processInvoiceReminder($invoice);

                if ($result === 'sent') {
                    $results['sent']++;
                } elseif ($result === 'skipped') {
                    $results['skipped']++;
                }

                $results['processed']++;
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error('Payment reminder error', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Traiter le rappel pour une facture
     */
    public function processInvoiceReminder(Invoice $invoice): string
    {
        // Calculer les jours de retard
        $daysOverdue = now()->diffInDays($invoice->due_date, false);

        if ($daysOverdue <= 0) {
            return 'skipped'; // Pas encore en retard
        }

        // Déterminer la phase appropriée
        $phase = $this->determinePhase($daysOverdue);

        if (!$phase) {
            return 'skipped'; // Trop tôt pour un rappel
        }

        // Vérifier si un rappel existe déjà pour cette phase
        $existingReminder = PaymentReminder::where('invoice_id', $invoice->id)
            ->where('phase', $phase)
            ->first();

        if ($existingReminder) {
            // Si déjà envoyé, on skip
            if ($existingReminder->status === 'sent') {
                return 'skipped';
            }

            // Si pending, on l'envoie
            if ($existingReminder->status === 'pending') {
                $this->sendReminder($existingReminder);
                return 'sent';
            }
        }

        // Créer un nouveau rappel
        $reminder = $this->createReminder($invoice, $phase, $daysOverdue);

        // Envoyer le rappel
        $this->sendReminder($reminder);

        return 'sent';
    }

    /**
     * Créer un rappel de paiement
     */
    public function createReminder(Invoice $invoice, string $phase, int $daysOverdue): PaymentReminder
    {
        $amountDue = $invoice->total_ttc - $invoice->amount_paid;
        $lateFeePercentage = PaymentReminder::PHASES_CONFIG[$phase]['late_fee_percentage'] ?? 0;
        $lateFee = round($amountDue * ($lateFeePercentage / 100), 2);

        return PaymentReminder::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'contract_id' => $invoice->contract_id,
            'phase' => $phase,
            'days_overdue' => $daysOverdue,
            'amount_due' => $amountDue,
            'late_fee' => $lateFee,
            'status' => 'pending',
            'message' => $this->generateMessage($phase, $invoice, $daysOverdue, $amountDue, $lateFee),
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'overdue_since' => now()->diffForHumans($invoice->due_date),
            ],
        ]);
    }

    /**
     * Envoyer un rappel
     */
    public function sendReminder(PaymentReminder $reminder): void
    {
        try {
            // Envoyer l'email
            $reminder->customer->notify(new PaymentReminderNotification($reminder));

            // Marquer comme envoyé
            $reminder->markAsSent(['email']);

            // Dispatch event
            event(new PaymentReminderSent($reminder));

            Log::info('Payment reminder sent', [
                'reminder_id' => $reminder->id,
                'invoice_id' => $reminder->invoice_id,
                'phase' => $reminder->phase,
                'customer_id' => $reminder->customer_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Obtenir les factures en retard
     */
    private function getOverdueInvoices(): Collection
    {
        return Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->whereColumn('amount_paid', '<', 'total_ttc')
            ->with(['customer', 'contract'])
            ->get();
    }

    /**
     * Déterminer la phase de rappel selon les jours de retard
     */
    private function determinePhase(int $daysOverdue): ?string
    {
        if ($daysOverdue >= 30) {
            return 'phase_3';
        } elseif ($daysOverdue >= 15) {
            return 'phase_2';
        } elseif ($daysOverdue >= 7) {
            return 'phase_1';
        }

        return null; // Pas encore de rappel nécessaire
    }

    /**
     * Générer le message du rappel
     */
    private function generateMessage(string $phase, Invoice $invoice, int $daysOverdue, float $amountDue, float $lateFee): string
    {
        $phaseName = PaymentReminder::PHASES_CONFIG[$phase]['name'] ?? $phase;
        $totalAmount = $amountDue + $lateFee;

        $messages = [
            'phase_1' => "Bonjour,\n\nNous constatons que votre facture {$invoice->invoice_number} d'un montant de {$amountDue}€ n'a pas encore été réglée.\n\nDate d'échéance : {$invoice->due_date->format('d/m/Y')}\nRetard : {$daysOverdue} jour(s)\n\nNous vous invitons à procéder au règlement dans les meilleurs délais.\n\nCordialement,\nL'équipe Boxibox",

            'phase_2' => "Bonjour,\n\nMalgré notre premier rappel, nous constatons que votre facture {$invoice->invoice_number} reste impayée.\n\nMontant dû : {$amountDue}€\nPénalités de retard (5%) : {$lateFee}€\nTotal à régler : {$totalAmount}€\n\nRetard : {$daysOverdue} jour(s)\n\nNous vous prions de bien vouloir régulariser votre situation sous 7 jours.\n\nCordialement,\nL'équipe Boxibox",

            'phase_3' => "Madame, Monsieur,\n\nMISE EN DEMEURE\n\nVotre facture {$invoice->invoice_number} demeure impayée malgré nos précédents rappels.\n\nMontant dû : {$amountDue}€\nPénalités de retard (10%) : {$lateFee}€\nTotal à régler : {$totalAmount}€\n\nRetard : {$daysOverdue} jour(s)\n\nNous vous mettons en demeure de régulariser votre situation sous 7 jours. À défaut, nous serons contraints d'engager des poursuites et de suspendre l'accès à votre box.\n\nService Recouvrement\nBoxibox",
        ];

        return $messages[$phase] ?? '';
    }

    /**
     * Marquer tous les rappels d'une facture comme payés
     */
    public function markInvoiceRemindersAsPaid(Invoice $invoice): void
    {
        $reminders = PaymentReminder::where('invoice_id', $invoice->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged'])
            ->get();

        foreach ($reminders as $reminder) {
            $reminder->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Dispatch event
            event(new PaymentReminderPaid($reminder));
        }
    }

    /**
     * Annuler tous les rappels d'une facture
     */
    public function cancelInvoiceReminders(Invoice $invoice): void
    {
        PaymentReminder::where('invoice_id', $invoice->id)
            ->whereIn('status', ['pending', 'sent'])
            ->update(['status' => 'cancelled']);
    }

    /**
     * Obtenir les statistiques des rappels
     */
    public function getStatistics(): array
    {
        return [
            'total' => PaymentReminder::count(),
            'by_phase' => [
                'phase_1' => PaymentReminder::phase('phase_1')->count(),
                'phase_2' => PaymentReminder::phase('phase_2')->count(),
                'phase_3' => PaymentReminder::phase('phase_3')->count(),
            ],
            'by_status' => [
                'pending' => PaymentReminder::pending()->count(),
                'sent' => PaymentReminder::sent()->count(),
                'paid' => PaymentReminder::paid()->count(),
            ],
            'total_amount_overdue' => PaymentReminder::overdue()->sum('amount_due'),
            'total_late_fees' => PaymentReminder::overdue()->sum('late_fee'),
        ];
    }

    /**
     * Créer un rappel manuel
     */
    public function createManualReminder(Invoice $invoice, string $phase, ?string $customMessage = null): PaymentReminder
    {
        $daysOverdue = max(0, now()->diffInDays($invoice->due_date, false));
        $amountDue = $invoice->total_ttc - $invoice->amount_paid;
        $lateFeePercentage = PaymentReminder::PHASES_CONFIG[$phase]['late_fee_percentage'] ?? 0;
        $lateFee = round($amountDue * ($lateFeePercentage / 100), 2);

        return PaymentReminder::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'contract_id' => $invoice->contract_id,
            'phase' => $phase,
            'days_overdue' => $daysOverdue,
            'amount_due' => $amountDue,
            'late_fee' => $lateFee,
            'status' => 'pending',
            'message' => $customMessage ?? $this->generateMessage($phase, $invoice, $daysOverdue, $amountDue, $lateFee),
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'manual' => true,
                'created_by' => auth()->id(),
            ],
        ]);
    }
}

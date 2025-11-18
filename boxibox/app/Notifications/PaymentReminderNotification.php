<?php

namespace App\Notifications;

use App\Models\PaymentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $reminder;

    /**
     * Create a new notification instance.
     */
    public function __construct(PaymentReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $reminder = $this->reminder;
        $invoice = $reminder->invoice;
        $config = $reminder->getPhaseConfig();

        $mail = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting($this->getGreeting())
            ->line($this->getIntroduction());

        // Détails de la facture
        $mail->line('**Facture** : ' . $invoice->invoice_number)
            ->line('**Date d\'échéance** : ' . $invoice->due_date->format('d/m/Y'))
            ->line('**Retard** : ' . $reminder->days_overdue . ' jour(s)');

        // Montants
        $mail->line('')
            ->line('**Montant dû** : ' . number_format($reminder->amount_due, 2, ',', ' ') . ' €');

        if ($reminder->late_fee > 0) {
            $mail->line('**Pénalités de retard** : ' . number_format($reminder->late_fee, 2, ',', ' ') . ' €')
                ->line('**Total à régler** : ' . number_format($reminder->total_amount, 2, ',', ' ') . ' €');
        }

        // Bouton d'action
        $mail->line('')
            ->action('Voir ma facture', route('invoices.show', $invoice));

        // Message de fin selon la phase
        $mail->line('')
            ->line($this->getClosing());

        // Style selon la phase
        if ($reminder->phase === 'phase_3') {
            $mail->error();
        } elseif ($reminder->phase === 'phase_2') {
            $mail->warning();
        }

        return $mail;
    }

    /**
     * Obtenir le sujet de l'email
     */
    private function getSubject(): string
    {
        return match($this->reminder->phase) {
            'phase_1' => '⚠️ Rappel : Facture ' . $this->reminder->invoice->invoice_number . ' en attente de paiement',
            'phase_2' => '🚨 Rappel urgent : Facture ' . $this->reminder->invoice->invoice_number . ' impayée',
            'phase_3' => '🔴 MISE EN DEMEURE : Facture ' . $this->reminder->invoice->invoice_number,
            default => 'Rappel de paiement',
        };
    }

    /**
     * Obtenir la salutation
     */
    private function getGreeting(): string
    {
        return match($this->reminder->phase) {
            'phase_1' => 'Bonjour,',
            'phase_2' => 'Bonjour,',
            'phase_3' => 'Madame, Monsieur,',
            default => 'Bonjour,',
        };
    }

    /**
     * Obtenir l'introduction
     */
    private function getIntroduction(): string
    {
        return match($this->reminder->phase) {
            'phase_1' => 'Nous constatons que votre facture n\'a pas encore été réglée. Ce message est un simple rappel amical.',
            'phase_2' => 'Malgré notre premier rappel, nous constatons que votre facture reste impayée. Des pénalités de retard ont été appliquées.',
            'phase_3' => 'MISE EN DEMEURE - Votre facture demeure impayée malgré nos précédents rappels. Nous sommes contraints d\'engager cette procédure formelle.',
            default => 'Nous vous contactons concernant une facture impayée.',
        };
    }

    /**
     * Obtenir la conclusion
     */
    private function getClosing(): string
    {
        return match($this->reminder->phase) {
            'phase_1' => 'Nous vous invitons à procéder au règlement dans les meilleurs délais. Si vous avez déjà effectué ce paiement, veuillez ne pas tenir compte de ce message.',
            'phase_2' => 'Nous vous prions de bien vouloir régulariser votre situation sous 7 jours. En cas de difficulté, n\'hésitez pas à nous contacter pour trouver une solution.',
            'phase_3' => 'Vous disposez de 7 jours pour régulariser votre situation. À défaut, nous serons contraints d\'engager des poursuites et de suspendre l\'accès à votre box.',
            default => 'Merci de votre compréhension.',
        };
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'invoice_id' => $this->reminder->invoice_id,
            'phase' => $this->reminder->phase,
            'amount_due' => $this->reminder->amount_due,
            'late_fee' => $this->reminder->late_fee,
            'total_amount' => $this->reminder->total_amount,
        ];
    }
}

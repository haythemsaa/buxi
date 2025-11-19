<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle facture - ' . $this->invoice->invoice_number)
            ->greeting('Bonjour ' . $this->invoice->customer->name . ',')
            ->line('Une nouvelle facture a été générée pour votre contrat.')
            ->line('')
            ->line('**Facture** : ' . $this->invoice->invoice_number)
            ->line('**Date d\'émission** : ' . $this->invoice->issue_date->format('d/m/Y'))
            ->line('**Date d\'échéance** : ' . $this->invoice->due_date->format('d/m/Y'))
            ->line('**Montant HT** : ' . number_format($this->invoice->subtotal_ht, 2, ',', ' ') . ' €')
            ->line('**TVA** : ' . number_format($this->invoice->tax_amount, 2, ',', ' ') . ' €')
            ->line('**Montant TTC** : ' . number_format($this->invoice->total_ttc, 2, ',', ' ') . ' €')
            ->line('')
            ->when($this->invoice->payment_method === 'sepa', function ($mail) {
                $mail->line('Le prélèvement SEPA sera effectué le ' . $this->invoice->due_date->format('d/m/Y') . '.');
            })
            ->action('Voir ma facture', url('/invoices/' . $this->invoice->id))
            ->line('Merci pour votre confiance !');
    }
}

<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRenewalReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Contract $contract,
        public int $daysBeforeExpiry
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
        $message = (new MailMessage)
            ->subject('Renouvellement de votre contrat - ' . $this->contract->contract_number)
            ->greeting('Bonjour ' . $this->contract->customer->name . ',');

        if ($this->daysBeforeExpiry > 0) {
            $message->line('Votre contrat arrive à échéance dans **' . $this->daysBeforeExpiry . ' jour(s)**.')
                ->line('Nous souhaitons vous rappeler que votre contrat de location n\'est pas automatiquement renouvelé.');
        } else {
            $message->line('Votre contrat est arrivé à échéance.')
                ->line('Votre box sera bientôt libéré si vous ne renouvelez pas votre contrat.');
        }

        return $message
            ->line('')
            ->line('**Contrat** : ' . $this->contract->contract_number)
            ->line('**Box** : ' . $this->contract->box->number)
            ->line('**Site** : ' . $this->contract->box->site->name)
            ->line('**Date de fin** : ' . ($this->contract->end_date ? $this->contract->end_date->format('d/m/Y') : 'Indéterminée'))
            ->line('**Loyer mensuel** : ' . number_format($this->contract->total_monthly_amount, 2, ',', ' ') . ' €')
            ->line('')
            ->line('Si vous souhaitez continuer à louer ce box, veuillez nous contacter au plus vite.')
            ->action('Renouveler mon contrat', url('/contracts/' . $this->contract->id))
            ->line('Notre équipe reste à votre disposition pour toute question.');
    }
}

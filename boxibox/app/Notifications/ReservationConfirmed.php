<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Reservation $reservation
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
            ->subject('Confirmation de votre réservation - ' . $this->reservation->reservation_number)
            ->greeting('Bonjour ' . ($this->reservation->customer?->name ?? $this->reservation->guest_first_name) . ',')
            ->line('Votre réservation a été confirmée avec succès !')
            ->line('**Numéro de réservation** : ' . $this->reservation->reservation_number)
            ->line('**Box** : ' . $this->reservation->box->number)
            ->line('**Site** : ' . $this->reservation->site->name)
            ->line('**Date de début** : ' . $this->reservation->start_date->format('d/m/Y'))
            ->line('**Durée** : ' . $this->reservation->duration_months . ' mois')
            ->line('**Montant du premier paiement** : ' . number_format($this->reservation->first_payment, 2, ',', ' ') . ' €')
            ->line('**Loyer mensuel** : ' . number_format($this->reservation->total_monthly_ttc, 2, ',', ' ') . ' € TTC')
            ->line('')
            ->line('**Validité de la réservation** : jusqu\'au ' . $this->reservation->expires_at->format('d/m/Y'))
            ->line('Vous devez confirmer cette réservation avant cette date pour concrétiser la location.')
            ->action('Voir ma réservation', url('/reservations/' . $this->reservation->id))
            ->line('Merci pour votre confiance !');
    }
}

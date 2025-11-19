<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Customer $customer
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
            ->subject('Bienvenue chez Boxibox !')
            ->greeting('Bonjour ' . $this->customer->name . ',')
            ->line('Nous sommes ravis de vous accueillir parmi nos clients Boxibox !')
            ->line('')
            ->line('Votre compte a été créé avec succès.')
            ->line('**Numéro client** : ' . $this->customer->customer_number)
            ->line('**Email** : ' . $this->customer->email)
            ->line('')
            ->line('**Vos avantages Boxibox** :')
            ->line('✓ Accès à nos espaces de stockage 24h/24')
            ->line('✓ Sécurité maximale avec vidéosurveillance')
            ->line('✓ Programme de fidélité avec points et réductions')
            ->line('✓ Application mobile pour gérer vos locations')
            ->line('✓ Service client disponible et à l\'écoute')
            ->line('')
            ->line('Vous avez gagné **100 points de fidélité** pour votre inscription !')
            ->action('Accéder à mon espace', url('/dashboard'))
            ->line('À très bientôt,')
            ->line('L\'équipe Boxibox');
    }
}

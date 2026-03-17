<?php

namespace App\Notifications;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealAssigned extends Notification
{
    use Queueable;

    public function __construct(public Deal $deal) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notification_preferences['deal_assigned'] ?? ['database' => true];
        $channels = [];
        if ($prefs['database'] ?? true) $channels[] = 'database';
        if ($prefs['email'] ?? false) $channels[] = 'mail';
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo negocio asignado: ' . $this->deal->title)
            ->line('Se te ha asignado el negocio: ' . $this->deal->title)
            ->action('Ver negocio', route('deals.show', $this->deal->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Se te asignó el negocio: ' . $this->deal->title,
            'deal_id' => $this->deal->id,
            'deal_title' => $this->deal->title,
            'type' => 'deal_assigned',
        ];
    }
}

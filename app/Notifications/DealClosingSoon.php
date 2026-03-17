<?php

namespace App\Notifications;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealClosingSoon extends Notification
{
    use Queueable;

    public function __construct(public Deal $deal, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notification_preferences['deal_closing_soon'] ?? ['database' => true, 'email' => true];
        $channels = [];
        if ($prefs['database'] ?? true) $channels[] = 'database';
        if ($prefs['email'] ?? true) $channels[] = 'mail';
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Negocio a punto de vencer: {$this->deal->title}")
            ->line("El negocio \"{$this->deal->title}\" cierra en {$this->daysLeft} día(s).")
            ->action('Ver negocio', route('deals.show', $this->deal->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Negocio \"{$this->deal->title}\" cierra en {$this->daysLeft} día(s).",
            'deal_id' => $this->deal->id,
            'type' => 'deal_closing_soon',
        ];
    }
}

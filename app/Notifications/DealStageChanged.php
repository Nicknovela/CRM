<?php

namespace App\Notifications;

use App\Models\Deal;
use App\Models\Stage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealStageChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Deal $deal,
        public ?Stage $oldStage,
        public Stage $newStage
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notification_preferences['deal_stage_changed'] ?? ['database' => true];
        $channels = [];
        if ($prefs['database'] ?? true) $channels[] = 'database';
        if ($prefs['email'] ?? false) $channels[] = 'mail';
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Negocio avanzó de etapa: ' . $this->deal->title)
            ->line(sprintf(
                'El negocio "%s" cambió de "%s" a "%s".',
                $this->deal->title,
                $this->oldStage?->name ?? 'N/A',
                $this->newStage->name
            ))
            ->action('Ver negocio', route('deals.show', $this->deal->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => sprintf(
                'Negocio "%s" avanzó a "%s"',
                $this->deal->title,
                $this->newStage->name
            ),
            'deal_id' => $this->deal->id,
            'type' => 'deal_stage_changed',
        ];
    }
}

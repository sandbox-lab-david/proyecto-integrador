<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class CorreoNotificacionPortal extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly string $titulo,
        public readonly string $mensaje,
        public readonly string $url,
    ) {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->titulo)
            ->greeting('Hola,')
            ->line(new HtmlString(nl2br(e($this->mensaje))))
            ->action('Ver detalle', url($this->url))
            ->salutation('Portal del estudiante');
    }
}

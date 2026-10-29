<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class NotificacionPortal extends Notification
{
    public function __construct(
        public readonly string $titulo,
        public readonly string $mensaje,
        public readonly string $url,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['titulo' => $this->titulo, 'mensaje' => $this->mensaje, 'url' => $this->url];
    }

}

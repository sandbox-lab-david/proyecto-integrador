<?php

namespace App\Services\Notificaciones;

use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\NotificacionPortal;
use InvalidArgumentException;
use LogicException;

class NotificacionService
{
    /** $observacion debe contener únicamente el texto visible para el estudiante. */
    public function notificarCambioEstado(Solicitud $s, EstadoSolicitud $nuevo, ?string $observacion = null): void
    {
        $inscripcion = $s->carreraEstudiante;
        if (! $inscripcion) {
            throw new LogicException('La solicitud no tiene una inscripción asociada.');
        }

        $usuarioId = $inscripcion->estudiante?->user_id;
        if (! $usuarioId) {
            throw new LogicException('No se pudo identificar al propietario de la solicitud.');
        }
        $usuario = User::findOrFail($usuarioId);
        $estado = $nuevo->getAttribute('etiqueta_estudiante') ?: $nuevo->nombre;
        $mensaje = 'Tu solicitud '.$s->codigo.' cambió al estado: '.$estado.'.';
        if (filled($observacion)) {
            $mensaje .= "\n\nObservación: ".trim($observacion);
        }

        $this->notificarUsuarios(
            [$usuario],
            'Solicitud '.$s->codigo.': '.$estado,
            $mensaje,
            route('solicitudes.show', $s->getKey(), false),
        );
    }

    /** @param iterable<User> $users */
    public function notificarUsuarios(iterable $users, string $titulo, string $mensaje, string $url): void
    {
        $destino = EnlaceNotificacion::local($url);
        if ($destino === null) {
            throw new InvalidArgumentException('El aviso debe enlazar a una página del portal.');
        }

        $enviados = [];
        foreach ($users as $usuario) {
            if (! $usuario instanceof User || ! $usuario->exists) {
                throw new InvalidArgumentException('El destinatario debe ser un usuario registrado.');
            }
            if (isset($enviados[$usuario->getKey()])) {
                continue;
            }
            $usuario->notify(new NotificacionPortal($titulo, $mensaje, $destino));
            $enviados[$usuario->getKey()] = true;
        }
    }
}

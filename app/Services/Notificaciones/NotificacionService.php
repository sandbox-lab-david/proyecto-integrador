<?php

namespace App\Services\Notificaciones;

use App\Models\EstadoSolicitud;
use App\Models\Estudiante;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\NotificacionPortal;
use App\Services\Academico\AutorizacionService;
use InvalidArgumentException;
use LogicException;

class NotificacionService
{
    public function __construct(private readonly AutorizacionService $autorizacion) {}

    /** $observacion debe contener únicamente el texto visible para el estudiante. */
    public function notificarCambioEstado(Solicitud $s, EstadoSolicitud $nuevo, ?string $observacion = null): void
    {
        $usuario = $this->propietario($s);
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

        $this->notificarPersonalDeRevision($s, $estado);
    }

    public function notificarResolucionGuardada(Solicitud $s, ?string $observacion = null): void
    {
        $mensaje = 'Se guardó una resolución pendiente para tu solicitud '.$s->codigo.'.';
        if (filled($observacion)) {
            $mensaje .= "\n\nObservación: ".trim($observacion);
        }
        $this->notificarUsuarios([$this->propietario($s)], 'Resolución pendiente · '.$s->codigo, $mensaje, route('solicitudes.show', $s->getKey(), false));
    }

    private function propietario(Solicitud $s): User
    {
        $inscripcion = $s->carreraEstudiante;
        if (! $inscripcion) {
            throw new LogicException('La solicitud no tiene una inscripción asociada.');
        }
        $estudianteId = $inscripcion->getAttribute('estudiante_id');
        $usuarioId = $estudianteId ? Estudiante::findOrFail($estudianteId)->user_id : $inscripcion->getAttribute('user_id');
        if (! $usuarioId) {
            throw new LogicException('No se pudo identificar al propietario de la solicitud.');
        }

        return User::findOrFail($usuarioId);
    }

    private function notificarPersonalDeRevision(Solicitud $solicitud, string $estado): void
    {
        $solicitud->loadMissing('carreraEstudiante.carreraModalidad.carrera');
        $carrera = $solicitud->carreraEstudiante?->carreraModalidad?->carrera;
        if (! $carrera) {
            return;
        }

        $destinatarios = $this->autorizacion->asistentesDeFacultad((int) $carrera->facultad_id)
            ->load('user')
            ->pluck('user')
            ->all();
        $director = $this->autorizacion->directorDeCarrera((int) $carrera->id)?->user;
        if ($director) {
            $destinatarios[] = $director;
        }
        $destinatarios = collect($destinatarios)
            ->filter(fn ($usuario) => $usuario instanceof User && $usuario->exists && $usuario->activo)
            ->unique(fn (User $usuario) => $usuario->getKey())
            ->values();

        if ($destinatarios->isEmpty()) {
            return;
        }

        $this->notificarUsuarios(
            $destinatarios,
            'Actualización de solicitud '.$solicitud->codigo,
            'La solicitud '.$solicitud->codigo.' cambió al estado: '.$estado.'.',
            route('revision.detalle', $solicitud->getKey(), false),
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

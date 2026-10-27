<?php

namespace App\Services\Flujo;

use App\Models\EstadoSolicitud;
use App\Models\HistorialSolicitud;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Notificaciones\NotificacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstadoSolicitudService
{
    public function estados(bool $soloActivos = true)
    {
        return EstadoSolicitud::orderBy('nombre')->get();
    }

    public function etiquetaParaEstudiante(string $codigoEstado)
    {
        return EstadoSolicitud::where('codigo', $codigoEstado)->value('nombre') ?: 'Desconocido';
    }

    public function transicionesPermitidas(string $codigoActual)
    {
        return DB::table('transiciones_estado')->where('estado_origen', $codigoActual)->where('activa', true)->pluck('estado_destino')->all();
    }

    /** C62: única puerta de escritura para cambios de estado y su historial. */
    public function transicionar(Solicitud $solicitud, string $codigoDestino, User $actor, ?string $obs = null, ?string $obsInterna = null): HistorialSolicitud
    {
        return DB::transaction(function () use ($solicitud, $codigoDestino, $actor, $obs, $obsInterna) {
            $solicitud = Solicitud::with('estadoSolicitud')->lockForUpdate()->findOrFail($solicitud->id);
            $origen = $solicitud->estadoSolicitud?->codigo;
            if (! $origen || ! in_array($codigoDestino, $this->transicionesPermitidas($origen), true)) {
                throw ValidationException::withMessages(['estado' => 'La transición solicitada no está permitida desde el estado actual.']);
            }
            $nuevo = EstadoSolicitud::where('codigo', $codigoDestino)->firstOrFail();
            $solicitud->forceFill(['estado_solicitud_id' => $nuevo->id])->save();
            $historial = HistorialSolicitud::create([
                'solicitud_id' => $solicitud->id,
                'estado_anterior' => $origen,
                'estado_nuevo' => $codigoDestino,
                'user_id' => $actor->id,
                'comentario' => $obs,
                'observacion_interna' => $obsInterna,
                'visible_estudiante' => true,
            ]);
            DB::afterCommit(fn () => app(NotificacionService::class)->notificarCambioEstado($solicitud->fresh(['carreraEstudiante']), $nuevo, $obs));

            return $historial;
        });
    }

    public function lineaDeTiempo(Solicitud $solicitud, bool $internas = false)
    {
        $consulta = $solicitud->historial()->with('user')->oldest('id');
        if (! $internas) {
            $consulta->select([
                'historial_solicitud.id',
                'historial_solicitud.solicitud_id',
                'historial_solicitud.estado_anterior',
                'historial_solicitud.estado_nuevo',
                'historial_solicitud.user_id',
                DB::raw('CASE WHEN historial_solicitud.visible_estudiante THEN historial_solicitud.comentario ELSE NULL END AS comentario'),
                'historial_solicitud.visible_estudiante',
                'historial_solicitud.created_at',
                'historial_solicitud.updated_at',
            ]);
        }

        return $consulta->get();
    }
}

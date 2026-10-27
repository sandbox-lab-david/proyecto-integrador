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
    public function transicionar(Solicitud $solicitud, string $destino, User $actor, ?string $comentario = null, bool $visibleEstudiante = true): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $destino, $actor, $comentario, $visibleEstudiante) {
            $solicitud = Solicitud::with('estadoSolicitud')->lockForUpdate()->findOrFail($solicitud->id);
            $origen = $solicitud->estadoSolicitud?->codigo;
            if (! $origen || ! in_array($destino, $this->transicionesPermitidas($origen), true)) {
                throw ValidationException::withMessages(['estado' => 'La transición solicitada no está permitida desde el estado actual.']);
            }
            $nuevo = EstadoSolicitud::where('codigo', $destino)->firstOrFail();
            $solicitud->forceFill(['estado_solicitud_id' => $nuevo->id])->save();
            HistorialSolicitud::create(['solicitud_id' => $solicitud->id, 'estado_anterior' => $origen, 'estado_nuevo' => $destino, 'user_id' => $actor->id, 'comentario' => $comentario, 'visible_estudiante' => $visibleEstudiante]);
            if ($visibleEstudiante) {
                DB::afterCommit(fn () => app(NotificacionService::class)->notificarCambioEstado($solicitud->fresh(['carreraEstudiante']), $nuevo, $comentario));
            }

            return $solicitud->fresh(['estadoSolicitud']);
        });
    }

    public function lineaDeTiempo(Solicitud $solicitud, bool $internas = false)
    {
        $consulta = $solicitud->historial()->with('user')->oldest('id');
        if (! $internas) {
            $consulta->where('visible_estudiante', true);
        }

        return $consulta->get();
    }
}

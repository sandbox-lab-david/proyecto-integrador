<?php

namespace App\Services\Solicitudes;

use App\Models\CarreraEstudiante;
use App\Models\EstadoSolicitud;
use App\Models\HistorialSolicitud;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use App\Models\User;
use App\Services\Catalogo\CatalogoTramites;
use Illuminate\Validation\ValidationException;

class IntegracionEnvioInstitucional implements IntegracionEnvio
{
    public function resolver(User $usuario, array $datos): array
    {
        if (! $usuario->activo || $usuario->tipo !== 'estudiante') {
            abort(403, 'Se requiere una cuenta de estudiante activa.');
        }
        $carreras = CarreraEstudiante::with('carrera')->where('user_id', $usuario->id)->where('estado', 'activo')->whereHas('carrera', fn ($q) => $q->where('activo', true))->get();
        $nombre = fn ($v) => mb_strtolower(trim($v));
        $coincidentes = $carreras->filter(fn ($i) => $nombre($i->carrera->nombre) === $nombre($datos['carrera']));
        if ($coincidentes->count() !== 1) {
            throw ValidationException::withMessages(['carrera' => 'La carrera declarada debe coincidir con una inscripción activa de tu cuenta.']);
        }
        $inscripcion = $coincidentes->first();
        $periodos = Periodo::where('activo', true)->whereDate('fecha_inicio', '<=', now(ReglasCampoSolicitud::zonaHoraria())->toDateString())->whereDate('fecha_fin', '>=', now(ReglasCampoSolicitud::zonaHoraria())->toDateString())->get()
            ->filter(fn ($p) => in_array($nombre($datos['periodo']), [$nombre($p->codigo), $nombre($p->nombre)], true));
        if ($periodos->count() !== 1) {
            throw ValidationException::withMessages(['periodo' => 'Indica el código o nombre de un período académico activo.']);
        }
        $codigo = app(CatalogoTramites::class)->buscar($datos['tramite'])['id'] ?? null;
        $tipo = TipoTramite::where('codigo', $codigo)->firstOrFail();

        return ['carrera_estudiante_id' => $inscripcion->id, 'tipo_tramite_id' => $tipo->id, 'periodo_id' => $periodos->first()->id, 'estado_solicitud_id' => EstadoSolicitud::where('codigo', 'borrador')->firstOrFail()->id, 'requisitos' => $tipo->documentos()->pluck('id', 'codigo')->all()];
    }

    public function enviar(Solicitud $solicitud, User $usuario): void
    {
        if (! $usuario->activo || $usuario->tipo !== 'estudiante'
            || (int) $solicitud->carreraEstudiante->user_id !== (int) $usuario->id) {
            abort(403);
        }
        $anterior = $solicitud->estadoSolicitud->codigo;
        abort_unless(in_array($anterior, ['borrador', 'observada', 'observado'], true), 409,
            'La solicitud no está disponible para enviar a revisión.');
        $estado = EstadoSolicitud::where('codigo', 'enviada')->firstOrFail();
        HistorialSolicitud::create(['solicitud_id' => $solicitud->id, 'estado_anterior' => $anterior, 'estado_nuevo' => 'enviada', 'user_id' => $usuario->id, 'comentario' => $anterior === 'borrador'
            ? 'Solicitud enviada para revisión de la facultad.'
            : 'Solicitud corregida y reenviada para revisión de la facultad.', 'visible_estudiante' => true]);
        $solicitud->forceFill(['estado_solicitud_id' => $estado->id])->save();
    }
}

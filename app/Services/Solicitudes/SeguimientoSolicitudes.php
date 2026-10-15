<?php

namespace App\Services\Solicitudes;

use App\Models\CarreraEstudiante;
use App\Models\EstadoSolicitud;
use App\Models\EvaluacionElegibilidad;
use App\Models\HistorialSolicitud;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use App\Services\Academico\AutorizacionService;
use App\Services\Academico\EstudianteService;
use App\Services\Flujo\EstadoSolicitudService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as Lista;

/**
 * Lo que «Mis solicitudes» (RF-2.13) muestra de las solicitudes guardadas:
 * las del estudiante de la sesión y las observaciones que puede leer.
 *
 * Está escrito contra los contratos de G1 (C11) y G3 (C64, C66) y todavía no
 * se ha ejecutado: esas clases no están en develop. Mientras falte alguna,
 * disponible() devuelve false y la pantalla solo lista lo que hay en la sesión.
 */
class SeguimientoSolicitudes
{
    /**
     * Clases de otros grupos que usan el listado, el detalle y SolicitudPolicy.
     *
     * @var list<class-string>
     */
    private const DEPENDENCIAS = [
        EstudianteService::class,
        AutorizacionService::class,
        CarreraEstudiante::class,
        EstadoSolicitudService::class,
        EstadoSolicitud::class,
        HistorialSolicitud::class,
        Resolucion::class,
        TipoTramite::class,
        EvaluacionElegibilidad::class,
    ];

    public function __construct(
        private readonly EstudianteService $estudiantes,
        private readonly SolicitudService $solicitudes,
        private readonly EstadoSolicitudService $estados,
    ) {}

    public static function disponible(): bool
    {
        foreach (self::DEPENDENCIAS as $clase) {
            if (! class_exists($clase)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Collection<int, Solicitud> de la más reciente a la más antigua
     */
    public function delEstudianteActual(): Collection
    {
        $estudiante = $this->estudiantes->estudianteActual();

        return $estudiante === null
            ? new Collection
            : $this->solicitudes->delEstudiante($estudiante->getKey());
    }

    /**
     * Observaciones que el personal dejó para el estudiante, de la más
     * reciente a la más antigua. Las internas nunca salen de aquí.
     *
     * @return Lista<int, array{fecha: mixed, estado: ?EstadoSolicitud, texto: string}>
     */
    public function observaciones(Solicitud $solicitud): Lista
    {
        $estados = $this->estados->estados();

        return $this->estados->lineaDeTiempo($solicitud, false)
            ->filter(fn ($paso) => filled($paso->observacion))
            ->sortByDesc('created_at')
            ->values()
            ->map(fn ($paso) => [
                'fecha' => $paso->created_at,
                'estado' => $estados->find($paso->estado_nuevo_id),
                'texto' => $paso->observacion,
            ]);
    }
}

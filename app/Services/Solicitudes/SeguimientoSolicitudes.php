<?php

namespace App\Services\Solicitudes;

use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Services\Flujo\EstadoSolicitudService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as Lista;
use Illuminate\Support\Facades\Schema;

/** Seguimiento estudiantil sobre expedientes persistidos y observaciones públicas. */
class SeguimientoSolicitudes
{
    public function __construct(private readonly EstadoSolicitudService $estados) {}

    public static function disponible(): bool
    {
        return Schema::hasTable('tipos_tramite')
            && Schema::hasTable('comprobaciones_informativas')
            && Schema::hasColumn('historial_solicitud', 'visible_estudiante')
            && method_exists(EstadoSolicitudService::class, 'lineaDeTiempo')
            && view()->exists('components.estado-badge') && view()->exists('components.linea-tiempo');
    }

    /**
     * @return Collection<int, Solicitud> de la más reciente a la más antigua
     */
    public function delEstudianteActual(): Collection
    {
        if (! auth()->check()) {
            return new Collection;
        }

        return Solicitud::whereHas('carreraEstudiante', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['tipoTramite', 'estadoSolicitud'])->latest('id')->get();
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
            ->filter(fn ($paso) => filled($paso->comentario))
            ->sortByDesc('created_at')
            ->values()
            ->map(fn ($paso) => [
                'fecha' => $paso->created_at,
                'estado' => $estados->firstWhere('codigo', $paso->estado_nuevo),
                'texto' => $paso->comentario,
            ]);
    }
}

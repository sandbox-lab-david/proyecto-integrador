<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\DocumentoRequerido;
use App\Models\EstadoSolicitud;
use App\Models\Periodo;
use App\Models\TipoTramite;
use Database\Seeders\CatalogoInformativoSeeder;
use Database\Seeders\EstadosSolicitudSeeder;

/**
 * Filas reales de G1, G3 y G4 para las llaves foráneas de las tablas de G2.
 * Salen de las factories de G1, del seeder del catálogo de G4 y del seeder de
 * estados y transiciones de G3 (regla 9 de modelos.xlsx).
 */
trait LlavesAjenasPrueba
{
    /**
     * Una inscripción y un periodo nuevos por llamada; trámite y estado del catálogo.
     *
     * @return array{carrera_estudiante_id: int, tipo_tramite_id: int, periodo_id: int, estado_solicitud_id: int}
     */
    protected function llavesAjenas(): array
    {
        return [
            'carrera_estudiante_id' => CarreraEstudiante::factory()->create()->id,
            'tipo_tramite_id' => $this->tipoTramite(),
            'periodo_id' => Periodo::factory()->create()->id,
            'estado_solicitud_id' => $this->estadoSolicitud(),
        ];
    }

    protected function tipoTramite(string $codigo = 'examen_recuperacion'): int
    {
        $this->sembrarCatalogo();

        return TipoTramite::where('codigo', $codigo)->firstOrFail()->id;
    }

    protected function estadoSolicitud(string $codigo = 'enviada'): int
    {
        $this->sembrarCatalogo();

        return EstadoSolicitud::where('codigo', $codigo)->firstOrFail()->id;
    }

    protected function documentoRequerido(string $tramite = 'examen_recuperacion'): int
    {
        return DocumentoRequerido::where('tipo_tramite_id', $this->tipoTramite($tramite))->orderBy('id')->firstOrFail()->id;
    }

    private function sembrarCatalogo(): void
    {
        if (! TipoTramite::query()->exists()) {
            $this->seed([CatalogoInformativoSeeder::class, EstadosSolicitudSeeder::class]);
        }
    }
}

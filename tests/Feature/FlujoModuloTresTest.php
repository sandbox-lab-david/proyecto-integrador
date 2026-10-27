<?php

namespace Tests\Feature;

use App\Models\EstadoSolicitud;
use App\Models\HistorialSolicitud;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Flujo\ResolucionService;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FlujoModuloTresTest extends TestCase
{
    use RefreshDatabase;

    public function test_transicion_valida_actualiza_estado_y_registra_historial(): void
    {
        $enviada = EstadoSolicitud::create(['codigo' => 'enviada', 'nombre' => 'Enviado por estudiante']);
        EstadoSolicitud::create(['codigo' => 'revisada', 'nombre' => 'Revisado']);
        \DB::table('transiciones_estado')->insert(['estado_origen' => 'enviada', 'estado_destino' => 'revisada', 'activa' => true, 'created_at' => now(), 'updated_at' => now()]);
        $solicitud = $this->crearSolicitud($enviada->id);
        $actor = User::factory()->create();

        app(EstadoSolicitudService::class)->transicionar($solicitud, 'revisada', $actor, 'Documentos verificados.', false);

        $this->assertSame('revisada', $solicitud->fresh()->estadoSolicitud->codigo);
        $this->assertDatabaseHas('historial_solicitud', ['solicitud_id' => $solicitud->id, 'estado_anterior' => 'enviada', 'estado_nuevo' => 'revisada', 'user_id' => $actor->id, 'visible_estudiante' => false]);
    }

    public function test_transicion_no_permitida_no_cambia_estado_ni_agrega_historial(): void
    {
        $enviada = EstadoSolicitud::create(['codigo' => 'enviada', 'nombre' => 'Enviado por estudiante']);
        EstadoSolicitud::create(['codigo' => 'ejecutada', 'nombre' => 'Ejecutado']);
        $solicitud = $this->crearSolicitud($enviada->id);
        $actor = User::factory()->create();

        try {
            app(EstadoSolicitudService::class)->transicionar($solicitud, 'ejecutada', $actor, 'Salto de estado.', false);
            $this->fail('La transición inválida debía rechazarse.');
        } catch (ValidationException) {
            $this->assertSame('enviada', $solicitud->fresh()->estadoSolicitud->codigo);
            $this->assertSame(0, HistorialSolicitud::where('solicitud_id', $solicitud->id)->count());
        }
    }

    public function test_resolucion_se_consulta_por_su_solicitud(): void
    {
        $estado = EstadoSolicitud::create(['codigo' => 'agendada', 'nombre' => 'Agendado']);
        $solicitud = $this->crearSolicitud($estado->id);
        $resolucion = Resolucion::create(['solicitud_id' => $solicitud->id, 'numero_resolucion' => 'RES-TEST-1', 'fecha_resolucion' => now()->toDateString(), 'decision' => 'Guardar']);

        $this->assertSame($resolucion->id, app(ResolucionService::class)->resolucionDe($solicitud)?->id);
    }

    public function test_filtro_acentuado_usa_sql_portable_sin_translate(): void
    {
        $query = RevisionSolicitudFilters::apply(Solicitud::query(), ['nombre' => 'José', 'codigo' => '', 'carrera' => 'Ingeniería', 'tramite' => '', 'estado' => '', 'fecha' => '']);
        $sql = strtolower($query->toSql());

        $this->assertStringNotContainsString('translate(', $sql);
        $this->assertStringContainsString('replace(', $sql);
    }

    private function crearSolicitud(int $estadoId): Solicitud
    {
        $id = \DB::table('solicitudes')->insertGetId(['codigo' => 'SOL-TEST-'.uniqid(), 'carrera_estudiante_id' => 1, 'tipo_tramite_id' => 1, 'periodo_id' => 1, 'estado_solicitud_id' => $estadoId, 'declaracion_veracidad' => true, 'created_at' => now(), 'updated_at' => now()]);

        return Solicitud::findOrFail($id);
    }
}

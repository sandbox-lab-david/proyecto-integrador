<?php

namespace Tests\Feature;

use App\Models\EstadoSolicitud;
use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use App\Models\HistorialSolicitud;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use App\Models\User;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Flujo\ResolucionService;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

        $historial = app(EstadoSolicitudService::class)->transicionar($solicitud, 'revisada', $actor, 'Documentos verificados.', 'Validación interna completada.');

        $this->assertSame('revisada', $solicitud->fresh()->estadoSolicitud->codigo);
        $this->assertInstanceOf(HistorialSolicitud::class, $historial);
        $this->assertDatabaseHas('historial_solicitud', ['solicitud_id' => $solicitud->id, 'estado_anterior' => 'enviada', 'estado_nuevo' => 'revisada', 'user_id' => $actor->id, 'comentario' => 'Documentos verificados.', 'observacion_interna' => 'Validación interna completada.', 'visible_estudiante' => true]);
        $publica = app(EstadoSolicitudService::class)->lineaDeTiempo($solicitud, false)->sole();
        $this->assertSame('Documentos verificados.', $publica->comentario);
        $this->assertArrayNotHasKey('observacion_interna', $publica->getAttributes());
        $completa = app(EstadoSolicitudService::class)->lineaDeTiempo($solicitud, true)->sole();
        $this->assertSame('Validación interna completada.', $completa->observacion_interna);
    }

    public function test_transicion_no_permitida_no_cambia_estado_ni_agrega_historial(): void
    {
        $enviada = EstadoSolicitud::create(['codigo' => 'enviada', 'nombre' => 'Enviado por estudiante']);
        EstadoSolicitud::create(['codigo' => 'ejecutada', 'nombre' => 'Ejecutado']);
        $solicitud = $this->crearSolicitud($enviada->id);
        $actor = User::factory()->create();

        try {
            app(EstadoSolicitudService::class)->transicionar($solicitud, 'ejecutada', $actor, 'Salto de estado.');
            $this->fail('La transición inválida debía rechazarse.');
        } catch (ValidationException) {
            $this->assertSame('enviada', $solicitud->fresh()->estadoSolicitud->codigo);
            $this->assertSame(0, HistorialSolicitud::where('solicitud_id', $solicitud->id)->count());
        }
    }

    public function test_el_seeder_incluye_borrador_y_el_envio_inicial(): void
    {
        $this->seed(\Database\Seeders\EstadosSolicitudSeeder::class);

        $this->assertDatabaseHas('estados_solicitud', ['codigo' => 'borrador', 'nombre' => 'Borrador']);
        $this->assertDatabaseHas('transiciones_estado', ['estado_origen' => 'borrador', 'estado_destino' => 'enviada', 'activa' => true]);
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
        $carrera = Carrera::factory()->create();
        $modalidad = Modalidad::factory()->create();
        $carreraModalidad = CarreraModalidad::create(['carrera_id' => $carrera->id, 'modalidad_id' => $modalidad->id]);
        $estudiante = Estudiante::factory()->create();
        $inscripcion = CarreraEstudiante::factory()->create([
            'estudiante_id' => $estudiante->id,
            'carrera_modalidad_id' => $carreraModalidad->id,
        ]);
        $periodo = Periodo::factory()->create();
        $tramite = TipoTramite::create([
            'codigo' => 'TEST-'.Str::upper(Str::random(8)),
            'nombre' => 'Trámite de prueba',
            'slug' => 'tramite-prueba-'.Str::lower(Str::random(8)),
            'contenido' => [],
        ]);

        return Solicitud::create([
            'codigo' => 'SOL-TEST-'.Str::upper(Str::random(8)),
            'carrera_estudiante_id' => $inscripcion->id,
            'tipo_tramite_id' => $tramite->id,
            'periodo_id' => $periodo->id,
            'estado_solicitud_id' => $estadoId,
            'declaracion_veracidad' => true,
        ]);
    }
}

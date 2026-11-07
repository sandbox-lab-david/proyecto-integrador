<?php

namespace Tests\Feature;

use App\Models\EstadoSolicitud;
use App\Models\Carrera;
use App\Models\CorteAgenda;
use App\Models\Facultad;
use App\Models\Trabajador;
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
use App\Notifications\NotificacionPortal;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Flujo\ResolucionService;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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

    public function test_una_solicitud_enviada_avisa_a_la_asistente_y_no_al_director(): void
    {
        $this->seed();
        Notification::fake();
        $solicitud = $this->solicitudDemo('borrador');

        app(EstadoSolicitudService::class)->transicionar($solicitud, 'enviada', $this->usuario('estudiante'));

        Notification::assertSentTo($this->usuario('asistente'), NotificacionPortal::class);
        Notification::assertNotSentTo($this->usuario('director'), NotificacionPortal::class);
    }

    public function test_una_solicitud_aprobada_por_la_asistente_avisa_al_director_y_no_a_la_asistente(): void
    {
        $this->seed();
        Notification::fake();
        $solicitud = $this->solicitudDemo('enviada');

        app(EstadoSolicitudService::class)->transicionar($solicitud, 'revisada', $this->usuario('asistente'));

        Notification::assertSentTo($this->usuario('director'), NotificacionPortal::class);
        Notification::assertNotSentTo($this->usuario('asistente'), NotificacionPortal::class);
    }

    public function test_una_observacion_no_avisa_al_personal(): void
    {
        $this->seed();
        Notification::fake();
        $solicitud = $this->solicitudDemo('enviada');

        app(EstadoSolicitudService::class)->transicionar($solicitud, 'observada', $this->usuario('asistente'), 'Falta un dato.');

        Notification::assertSentTo($this->usuario('estudiante'), NotificacionPortal::class);
        Notification::assertNotSentTo($this->usuario('asistente'), NotificacionPortal::class);
        Notification::assertNotSentTo($this->usuario('director'), NotificacionPortal::class);
    }

    public function test_la_asistente_registra_la_resolucion_y_la_ejecucion_de_su_facultad(): void
    {
        $this->seed();
        [$solicitud, $corte] = $this->solicitudAgendada();
        $asistente = $this->usuario('asistente');

        $this->actingAs($asistente)
            ->post(route('revision.agendas.resoluciones.guardar', [$corte->id, $solicitud->id]), ['decision' => 'Aprobado'])
            ->assertRedirect(route('revision.agendas.resoluciones', $corte->id));
        $this->assertSame('aprobada_ejecucion', $solicitud->fresh()->estadoSolicitud->codigo);
        $this->assertDatabaseHas('resoluciones', ['solicitud_id' => $solicitud->id, 'decision' => 'Aprobado', 'registrada_por' => $asistente->id]);

        $this->actingAs($asistente)
            ->post(route('revision.resoluciones.estado', $solicitud->id), ['accion' => 'ejecutar'])
            ->assertRedirect(route('revision.resoluciones'));
        $this->assertSame('ejecutada', $solicitud->fresh()->estadoSolicitud->codigo);
    }

    public function test_una_asistente_de_otra_facultad_no_puede_registrar_la_resolucion(): void
    {
        $this->seed();
        [$solicitud, $corte] = $this->solicitudAgendada();
        $otra = $this->asistenteDe(Facultad::where('codigo', 'ARQ')->firstOrFail());

        $this->actingAs($otra)
            ->post(route('revision.agendas.resoluciones.guardar', [$corte->id, $solicitud->id]), ['decision' => 'Aprobado'])
            ->assertForbidden();
        $this->actingAs($otra)
            ->post(route('revision.resoluciones.estado', $solicitud->id), ['accion' => 'ejecutar'])
            ->assertForbidden();
        $this->assertSame('agendada', $solicitud->fresh()->estadoSolicitud->codigo);
    }

    public function test_consulta_ve_las_resoluciones_pero_no_puede_registrarlas(): void
    {
        $this->seed();
        [$solicitud, $corte] = $this->solicitudAgendada();
        $consulta = $this->usuario('consulta');

        $this->actingAs($consulta)
            ->get(route('revision.agendas.resoluciones', $corte->id))
            ->assertOk()
            ->assertSee('Vista de solo lectura.');
        $this->actingAs($consulta)
            ->post(route('revision.agendas.resoluciones.guardar', [$corte->id, $solicitud->id]), ['decision' => 'Aprobado'])
            ->assertForbidden();
        $this->assertSame('agendada', $solicitud->fresh()->estadoSolicitud->codigo);
    }

    private function usuario(string $nombre): User
    {
        return User::where('email', $nombre.'@uees.edu.ec')->firstOrFail();
    }

    private function solicitudDemo(string $estado): Solicitud
    {
        $inscripcion = $this->usuario('estudiante')->estudiante->carrerasEstudiante()->firstOrFail();

        return Solicitud::create([
            'codigo' => 'SOL-TEST-'.Str::upper(Str::random(8)),
            'carrera_estudiante_id' => $inscripcion->id,
            'tipo_tramite_id' => TipoTramite::query()->value('id'),
            'periodo_id' => Periodo::where('activo', true)->value('id'),
            'estado_solicitud_id' => EstadoSolicitud::where('codigo', $estado)->value('id'),
            'declaracion_veracidad' => true,
        ]);
    }

    /** @return array{0: Solicitud, 1: CorteAgenda} */
    private function solicitudAgendada(): array
    {
        $solicitud = $this->solicitudDemo('agendada');
        $corte = CorteAgenda::create([
            'facultad_id' => Facultad::where('codigo', 'ING')->value('id'),
            'fecha_sesion' => now()->addWeek()->toDateString(),
            'estado' => 'Agendado',
        ]);
        $corte->solicitudes()->attach($solicitud->id);

        return [$solicitud, $corte];
    }

    private function asistenteDe(Facultad $facultad): User
    {
        $usuario = User::factory()->create(['tipo' => 'trabajador', 'activo' => true, 'email' => 'otra.asistente@uees.edu.ec']);
        $trabajador = Trabajador::create([
            'user_id' => $usuario->id,
            'codigo' => 'T-OTRA',
            'nombres' => 'Otra',
            'apellidos' => 'Asistente',
            'correo' => $usuario->email,
            'activo' => true,
        ]);
        DB::table('rol_trabajador')->insert(['trabajador_id' => $trabajador->id, 'rol' => 'asistente']);
        DB::table('facultad_trabajador')->insert(['trabajador_id' => $trabajador->id, 'facultad_id' => $facultad->id]);

        return $usuario;
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

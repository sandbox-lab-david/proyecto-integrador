<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use App\Models\Modalidad;
use App\Models\EstadoSolicitud;
use App\Models\HistorialSolicitud;
use App\Models\Periodo;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use App\Models\User;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeguimientoEstudianteTest extends TestCase
{
    use RefreshDatabase;

    private User $estudiante;
    private Solicitud $propia;
    private Solicitud $ajena;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(CatalogoInformativoSeeder::class);
        $this->estudiante = User::factory()->create(['tipo' => 'estudiante', 'activo' => true]);
        $otro = User::factory()->create(['tipo' => 'estudiante', 'activo' => true]);
        $facultad = DB::table('facultades')->insertGetId(['codigo' => 'ING', 'nombre' => 'Ingeniería', 'activo' => true]);
        $carrera = Carrera::create(['facultad_id' => $facultad, 'codigo' => 'COM', 'nombre' => 'Computación', 'nivel' => 'grado', 'activo' => true]);
        $periodo = Periodo::create(['codigo' => '2026-2', 'nombre' => 'Período actual', 'tipo' => 'ordinario', 'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(30), 'semana_primer_parcial' => 4, 'activo' => true]);
        $carreraModalidad = CarreraModalidad::create(['carrera_id' => $carrera->id, 'modalidad_id' => Modalidad::firstOrCreate(['nombre' => 'Presencial'])->id]);
        foreach ([$this->estudiante, $otro] as $indice => $usuario) {
            $estudiante = Estudiante::create(['user_id' => $usuario->id, 'codigo' => '202600000'.$indice, 'nombres' => 'Estudiante', 'apellidos' => 'Prueba '.$indice, 'correo' => $usuario->email]);
            $inscripcion = CarreraEstudiante::create(['estudiante_id' => $estudiante->id, 'carrera_modalidad_id' => $carreraModalidad->id, 'estado' => 'activo']);
            $solicitud = Solicitud::create(['codigo' => 'SOL-SEGUIMIENTO-'.$indice,
                'carrera_estudiante_id' => $inscripcion->id, 'periodo_id' => $periodo->id,
                'tipo_tramite_id' => TipoTramite::where('codigo', 'examen_recuperacion')->firstOrFail()->id,
                'estado_solicitud_id' => EstadoSolicitud::where('codigo', 'enviada')->firstOrFail()->id,
                'detalle' => ['tramite' => 'recuperacion'], 'declaracion_veracidad' => true, 'enviada_at' => now()]);
            if ($indice === 0) $this->propia = $solicitud;
            else $this->ajena = $solicitud;
        }
        $this->actingAs($this->estudiante);
    }

    public function test_lista_solo_las_propias_con_estado_y_enlace_al_seguimiento(): void
    {
        $this->get(route('solicitudes.index'))->assertOk()
            ->assertSee($this->propia->codigo)->assertDontSee($this->ajena->codigo)
            ->assertSee('Enviada')->assertSee(route('solicitudes.show', $this->propia), false);
    }

    public function test_historial_y_observaciones_muestran_solo_comentarios_publicos_escapados(): void
    {
        HistorialSolicitud::create(['solicitud_id' => $this->propia->id, 'estado_nuevo' => 'enviada',
            'comentario' => 'Primer paso público', 'visible_estudiante' => true]);
        HistorialSolicitud::create(['solicitud_id' => $this->propia->id, 'estado_nuevo' => 'enviada',
            'comentario' => 'Nota interna reservada', 'visible_estudiante' => false]);
        HistorialSolicitud::create(['solicitud_id' => $this->propia->id, 'estado_nuevo' => 'enviada',
            'comentario' => 'Adjunta certificado <script>alert(1)</script>', 'visible_estudiante' => true]);
        $respuesta = $this->get(route('solicitudes.show', $this->propia))->assertOk()
            ->assertSee('Observaciones')->assertSee('Línea de tiempo')->assertSee('Enviada')
            ->assertSee('Primer paso público')->assertSee('Adjunta certificado &lt;script&gt;', false)
            ->assertDontSee('Nota interna reservada')->assertDontSee('<script>alert(1)</script>', false);
        $this->assertCount(2, $respuesta->viewData('observaciones'));
        $this->assertStringStartsWith('Adjunta certificado', $respuesta->viewData('observaciones')[0]['texto']);
    }

    public function test_muestra_los_datos_enviados_con_el_resumen_de_la_solicitud(): void
    {
        $this->propia->forceFill(['detalle' => ['tramite' => 'recuperacion', 'nombre' => 'Estudiante Prueba 0',
            'campos' => ['gpa_general' => '88']]])->save();
        $this->get(route('solicitudes.show', $this->propia))->assertOk()
            ->assertSeeInOrder(['Datos enviados', 'Nombres y apellidos', 'Estudiante Prueba 0', 'GPA general', '88'])
            ->assertSee('resumen-solicitud--sin-marco', false);
    }

    public function test_no_puede_abrir_expediente_ajeno(): void
    {
        $this->get(route('solicitudes.show', $this->ajena))->assertForbidden();
    }

    public function test_cuenta_inactiva_no_lista_ni_abre_expedientes(): void
    {
        $this->estudiante->forceFill(['activo' => false])->save();
        $this->get(route('solicitudes.index'))->assertOk()->assertDontSee($this->propia->codigo);
        $this->get(route('solicitudes.show', $this->propia))->assertForbidden();
    }

    public function test_sin_resolucion_no_inventa_una_decision(): void
    {
        $this->get(route('solicitudes.show', $this->propia))->assertOk()
            ->assertDontSee('Resolución final')->assertSee('La facultad no ha dejado observaciones');
    }

    public function test_resolucion_muestra_numero_fecha_y_observacion_del_modelo_actual(): void
    {
        Resolucion::create(['solicitud_id' => $this->propia->id, 'numero_resolucion' => 'RES-2026-043',
            'fecha_resolucion' => '2026-10-15', 'decision' => 'Aprobado', 'observacion' => 'Resolución registrada por la facultad',
            'documento_pdf' => 'resoluciones/RES-2026-043.pdf']);
        $this->get(route('solicitudes.show', $this->propia))->assertOk()
            ->assertSee('Resolución final')->assertSee('RES-2026-043')->assertSee('15/10/2026')
            ->assertSee('Aprobado')->assertSee('Resolución registrada por la facultad')
            ->assertSee('Resolución RES-2026-043.pdf')->assertSee('/archivos/ver?', false)
            ->assertSee('signature=', false);
    }

    public function test_observada_ofrece_corregir_y_enviada_no(): void
    {
        $this->get(route('solicitudes.show', $this->propia))->assertOk()->assertDontSee('Corregir solicitud');
        $estado = EstadoSolicitud::create(['codigo' => 'observada', 'nombre' => 'Observada']);
        $this->propia->forceFill(['estado_solicitud_id' => $estado->id])->save();
        $this->get(route('solicitudes.index'))->assertOk()->assertSee('Observada')
            ->assertSee(route('solicitudes.edit', $this->propia), false);
        $this->get(route('solicitudes.show', $this->propia))->assertOk()->assertSee('Corregir solicitud');
    }
}

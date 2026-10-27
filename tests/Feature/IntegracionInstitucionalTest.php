<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use App\Models\HistorialSolicitud;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\Trabajador;
use App\Models\User;
use App\Policies\SolicitudPolicy;
use App\Services\Academico\AutorizacionService;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Solicitudes\CatalogoFacultades;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\IntegracionEnvio;
use App\Services\Solicitudes\TramitesSimulados;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class IntegracionInstitucionalTest extends TestCase
{
    use ArchivosDePrueba, RefreshDatabase, RequisitosPrologPrueba;

    private function preparar(): array
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $u = User::factory()->create(['tipo' => 'estudiante']);
        $f = DB::table('facultades')->insertGetId(['codigo' => 'ING', 'nombre' => 'Ingeniería', 'activo' => true]);
        $c = Carrera::create(['facultad_id' => $f, 'codigo' => 'COM', 'nombre' => 'Ingeniería en Computación', 'nivel' => 'grado', 'activo' => true]);
        $p = Periodo::create(['codigo' => 'ACTUAL', 'nombre' => 'Período actual', 'tipo' => 'ordinario', 'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(30), 'semana_primer_parcial' => 4, 'activo' => true]);
        $e = Estudiante::create(['user_id' => $u->id, 'codigo' => '2026000001', 'nombres' => 'Estudiante', 'apellidos' => 'Prueba', 'correo' => $u->email]);
        $cm = CarreraModalidad::create(['carrera_id' => $c->id, 'modalidad_id' => Modalidad::create(['nombre' => 'Presencial'])->id]);
        $i = CarreraEstudiante::create(['estudiante_id' => $e->id, 'carrera_modalidad_id' => $cm->id, 'estado' => 'activo']);

        return [$u, $i, ['tramite' => 'recuperacion', 'carrera' => 'Ingeniería en Computación', 'periodo' => 'ACTUAL']];
    }

    public function test_resuelve_contexto_real_y_registra_envio_visible(): void
    {
        [$u,$i,$datos] = $this->preparar();
        $servicio = app(IntegracionEnvio::class);
        $ctx = $servicio->resolver($u, $datos);
        $this->assertSame($i->id, $ctx['carrera_estudiante_id']);
        $s = Solicitud::create($ctx + ['codigo' => 'SOL-TEST', 'detalle' => $datos, 'declaracion_veracidad' => true, 'enviada_at' => now()]);
        $servicio->enviar($s, $u);
        $this->assertSame('enviada', $s->fresh()->estadoSolicitud->codigo);
        $this->assertSame(1, app(EstadoSolicitudService::class)->lineaDeTiempo($s, false)->count());
        HistorialSolicitud::create(['solicitud_id' => $s->id, 'estado_nuevo' => 'enviada', 'comentario' => 'Nota interna', 'visible_estudiante' => false]);
        $this->assertSame(1, app(EstadoSolicitudService::class)->lineaDeTiempo($s, false)->count());
        $this->actingAs($u)->get('/estudiante/solicitudes/'.$s->id)->assertOk()->assertDontSee('Nota interna');
        $this->actingAs(User::factory()->create())->get('/estudiante/solicitudes/'.$s->id)->assertForbidden();
        $this->actingAs($u)->get('/estudiante/solicitudes')->assertOk()->assertSee('SOL-TEST');
    }

    public function test_nombre_y_correo_declarados_no_eligen_al_propietario(): void
    {
        [$u,$i,$datos] = $this->preparar();
        $otro = User::factory()->create(['tipo' => 'estudiante']);
        $this->expectException(ValidationException::class);
        app(IntegracionEnvio::class)->resolver($otro, $datos + ['correo' => $u->email, 'nombre' => $u->name]);
    }

    public function test_no_se_inventa_periodo_ni_inscripcion(): void
    {
        [$u,$i,$datos] = $this->preparar();
        $datos['periodo'] = 'No existe';
        $this->expectException(ValidationException::class);
        app(IntegracionEnvio::class)->resolver($u, $datos);
    }

    public function test_nombre_admin_y_dominio_no_otorgan_permisos(): void
    {
        $u = User::factory()->create(['name' => 'admin', 'email' => 'admin@admin.uees.edu.ec', 'tipo' => 'trabajador']);
        Route::get('/prueba-rol', fn () => response('permitido'))->middleware(['web', 'rol:admin']);
        $this->actingAs($u)->get('/prueba-rol')->assertForbidden();
    }

    public function test_envia_los_veinte_tramites_con_integracion_real_y_reintento_sin_duplicados(): void
    {
        Storage::fake('local');
        [$u,$inscripcion,$base] = $this->preparar();
        $this->actingAs($u);
        $materiaId = DB::table('materias')->insertGetId(['codigo' => 'BD401', 'nombre' => 'Bases de datos', 'creditos' => 3]);
        $malla = \App\Models\Malla::factory()->create();
        $malla->materias()->attach($materiaId, ['nivel' => 1]);
        $inscripcion->update(['malla_id' => $malla->id]);
        $curso = \App\Models\Curso::create(['materia_id' => $materiaId,
            'periodo_id' => Periodo::where('codigo', 'ACTUAL')->value('id'), 'paralelo' => 'A']);
        $catalogo = app(TramitesSimulados::class);
        foreach (array_keys(DocumentoSolicitud::PLANTILLAS) as $codigo) {
            $def = $catalogo->buscar($codigo);
            $valores = function ($campos) {
                $datos = [];
                foreach ($campos as $campo) {
                    if (isset($campo['mostrar_si'])) {
                        continue;
                    }
                    $datos[$campo['nombre']] = match ($campo['tipo']) {
                        'numero' => str_contains($campo['nombre'], 'anio') ? '2025' : (str_contains($campo['nombre'], 'semana') ? '1' : '80'),
                        'fecha' => '2025-01-01','hora' => '08:30','lista' => (string) array_key_first($campo['opciones']),default => 'Información de prueba',
                    };
                }

                return $datos;
            };
            $campos = $valores($def['campos']);
            if ($codigo === 'cambio-modalidad') {
                $campos['modalidad_actual'] = 'Presencial';
                $campos['modalidad_destino'] = 'En línea';
            }
            if ($codigo === 'tercer-registro') {
                $materiaDatos = ['registro_1_anio' => '2024', 'registro_1_periodo' => 'I', 'registro_1_nota' => '50', 'registro_2_anio' => '2025', 'registro_2_periodo' => 'II', 'registro_2_nota' => '55'];
            } else {
                $materiaDatos = $valores($def['campos_materia']);
            }
            $datos = CatalogoFacultades::normalizar(array_replace($base, ['tramite' => $codigo, 'nombre' => $u->name, 'correo' => $u->email, 'codigo' => '2026000012', 'cedula' => '0912345678', 'celular' => '0991234567', 'facultad' => 'Ingeniería', 'modalidad' => 'Presencial', 'campos' => $campos, 'declaracion' => true, 'materias' => $def['max_materias'] > 0 ? [['id' => 'fila-1', 'codigo' => 'MAT101', 'nombre' => 'Materia de prueba', 'periodo' => '2025-I', 'paralelo' => 'A', 'docente' => 'Docente de prueba', 'datos' => $materiaDatos]] : []]));
            $datos['origen_materias'] = 'catalogo';
            if ($def['max_materias'] > 0) {
                $datos['materias'][0]['id'] = (string) $curso->id;
            }
            $datos = array_replace($datos, app(\App\Services\Solicitudes\MateriasSolicitud::class)->entrada($datos));
            $temporales = app(DocumentosTemporales::class);
            $temporales->guardar($codigo, app(DocumentoSolicitud::class)->generarWord($datos), $datos);
            $datos['clave_envio'] = $temporales->buscar($codigo)['version'];
            app(FirmadosTemporales::class)->guardar($this->pdf(), $codigo);
            $datos['requisitos'] = [];
            foreach ($def['documentos'] as $doc) {
                if (! $doc['obligatorio']) {
                    continue;
                }
                $archivo = app(RespaldosTemporales::class)->agregar($this->pdf(), $codigo);
                $datos['requisitos'][$archivo['id']] = $doc['id'];
            }
            $primera = $this->postJson(route('solicitudes.enviar'), $datos)->assertCreated()->json();
            $this->assertSame($primera, $this->postJson(route('solicitudes.enviar'), $datos)->assertCreated()->json());
            $expediente = Solicitud::where('codigo', $primera['codigo'])->firstOrFail();
            if ($def['max_materias'] > 0) {
                $this->assertSame($curso->id, $expediente->materias()->firstOrFail()->curso_id, $codigo);
                $this->assertSame('Bases de datos', $expediente->detalle['materias'][0]['nombre'], $codigo);
            } else {
                $this->assertSame(0, $expediente->materias()->count(), $codigo);
            }
            // Respeta el límite real de envíos por minuto sin desactivar el middleware.
            $this->travel(61)->seconds();
        }
        $this->assertDatabaseCount('solicitudes', 20);
        $this->assertDatabaseCount('historial_solicitud', 20);
        $this->assertSame(20, Solicitud::whereHas('estadoSolicitud', fn ($q) => $q->where('codigo', 'enviada'))->count());
    }

    public function test_director_solo_ve_su_carrera_con_asignacion_vigente(): void
    {
        [$u,$i,$datos] = $this->preparar();
        $ctx = app(IntegracionEnvio::class)->resolver($u, $datos);
        $s = Solicitud::create($ctx + ['codigo' => 'SOL-DIRECTOR', 'detalle' => $datos, 'declaracion_veracidad' => true, 'enviada_at' => now()]);
        $director = User::factory()->create(['tipo' => 'trabajador']);
        $t = Trabajador::create(['user_id' => $director->id, 'codigo' => 'DIR-1', 'nombres' => 'Director', 'apellidos' => 'Prueba', 'correo' => $director->email, 'activo' => true]);
        $id = DB::table('directores_carrera')->insertGetId(['trabajador_id' => $t->id, 'carrera_id' => $i->carrera_id, 'fecha_inicio' => today()->subDays(20)]);
        $this->assertTrue(app(SolicitudPolicy::class)->view($director, $s));
        DB::table('directores_carrera')->where('id', $id)->update(['fecha_fin' => today()->subDays(10)]);
        $this->assertFalse(app(SolicitudPolicy::class)->view($director, $s));
        DB::table('rol_trabajador')->insert(['trabajador_id' => $t->id, 'rol' => 'admin']);
        $this->assertTrue(app(AutorizacionService::class)->tieneRol($director, 'admin'));
    }
}

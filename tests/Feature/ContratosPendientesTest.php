<?php

namespace Tests\Feature;

use App\Enums\EstadoInscripcion;
use App\Enums\NivelCarrera;
use App\Jobs\ProcesarImportacion;
use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\Curso;
use App\Models\DirectorCarrera;
use App\Models\Facultad;
use App\Models\Importacion;
use App\Models\Trabajador;
use App\Models\User;
use App\Notifications\NotificacionPortal;
use App\Services\Academico\AutorizacionService;
use App\Services\Academico\EstudianteService;
use App\Services\Academico\ImportacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContratosPendientesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_c12_perfil_del_estudiante(): void
    {
        $estudiante = User::where('email', 'estudiante@uees.edu.ec')->first()->estudiante;

        $perfil = app(EstudianteService::class)->perfil($estudiante->id);

        $this->assertSame('2026000001', $perfil['codigo']);
        $this->assertSame('Estudiante de Prueba', $perfil['nombre_completo']);
        $this->assertArrayNotHasKey('gpa', $perfil);
        $this->assertCount(1, $perfil['carreras']);

        $carrera = $perfil['carreras'][0];
        $this->assertSame('COMP', $carrera['carrera']['codigo']);
        $this->assertSame('ING', $carrera['facultad']['codigo']);
        $this->assertSame('Presencial', $carrera['modalidad']['nombre']);
        $this->assertSame('2024', $carrera['malla']['version']);
        $this->assertSame('2025-05-05', $carrera['fecha_inscripcion']);
        $this->assertSame('activo', $carrera['estado']);

        CarreraEstudiante::query()->update(['estado' => EstadoInscripcion::EGRESADO->value]);
        $this->assertSame([], app(EstudianteService::class)->perfil($estudiante->id)['carreras']);
    }

    public function test_c17_y_c18_solo_cuentan_directores_vigentes(): void
    {
        $this->travelTo('2026-10-21 10:00');
        $servicio = app(AutorizacionService::class);
        $computacion = Carrera::where('codigo', 'COMP')->first();
        $medicina = Carrera::where('codigo', 'MED')->first();
        $diego = Trabajador::where('codigo', 'DIR-001')->first();

        DirectorCarrera::create(['carrera_id' => $medicina->id, 'trabajador_id' => $diego->id, 'fecha_inicio' => '2025-01-01', 'fecha_fin' => '2025-12-31']);

        $this->assertSame(['Ingeniería en Computación'], $servicio->carrerasQueDirige($diego)->pluck('nombre')->all());
        $this->assertSame(['Ingeniería en Computación'], $servicio->carrerasQueDirige($diego->id)->pluck('nombre')->all());

        DirectorCarrera::where('carrera_id', $computacion->id)->update(['fecha_fin' => '2026-10-21']);
        $this->assertSame($diego->id, $servicio->directorDeCarrera($computacion->id)?->id);

        $nuevo = Trabajador::where('codigo', 'DOC-001')->first();
        DirectorCarrera::create(['carrera_id' => $computacion->id, 'trabajador_id' => $nuevo->id, 'fecha_inicio' => '2026-10-22']);
        $this->assertSame($diego->id, $servicio->directorDeCarrera($computacion->id)?->id);

        $this->travelTo('2026-10-22 10:00');
        $this->assertSame($nuevo->id, $servicio->directorDeCarrera($computacion->id)?->id);
        $this->assertTrue($servicio->carrerasQueDirige($diego)->isEmpty());
        $this->assertNull($servicio->directorDeCarrera($medicina->id));
    }

    public function test_c35_devuelve_el_formato_del_excel(): void
    {
        $curso = Curso::whereHas('materia', fn ($q) => $q->where('codigo', 'MAT101'))->first();

        $this->actingAs(User::where('email', 'estudiante@uees.edu.ec')->first())
            ->getJson(route('interno.cursos', ['materia_id' => $curso->materia_id, 'periodo_id' => $curso->periodo_id]))
            ->assertOk()
            ->assertExactJson([[
                'id' => $curso->id,
                'paralelo' => 'A1',
                'cupo' => 35,
                'profesor' => ['id' => $curso->profesor_id, 'nombres' => 'Luis', 'apellidos' => 'Paredes'],
            ]]);

        $this->actingAs(User::where('email', 'estudiante@uees.edu.ec')->first())
            ->getJson(route('interno.cursos'))->assertUnprocessable()->assertJsonValidationErrors(['materia_id', 'periodo_id']);
    }

    public function test_c38_selects_compartidos(): void
    {
        $this->actingAs(User::where('email', 'admin@uees.edu.ec')->first());
        $ingenieria = Facultad::where('codigo', 'ING')->value('id');

        $facultad = $this->blade('<x-select-facultad :selected="$id" />', ['id' => $ingenieria]);
        $facultad->assertSee('class="ui-control"', false)->assertSee('Ingeniería')->assertSee('selected', false);

        $carrera = $this->blade('<x-select-carrera :facultad-id="$id" />', ['id' => $ingenieria]);
        $carrera->assertSee('Ingeniería en Computación')->assertDontSee('Medicina');

        $dependiente = $this->blade('<x-select-carrera depende-de="facultad_id" />');
        $dependiente->assertSee('data-depende-de="facultad_id"', false)->assertSee(route('interno.carreras'), false)->assertDontSee('Ingeniería en Computación');

        $this->travelTo('2026-10-21');
        $periodo = $this->blade('<x-select-periodo />');
        $periodo->assertSee('Ordinario II 2026 (activo)')->assertSee('Ordinario I 2026');
        $this->assertMatchesRegularExpression('/<option value="\d+" selected>Ordinario II 2026/', (string) $periodo);
    }

    public function test_c40_enums_coinciden_con_los_datos(): void
    {
        $this->assertEqualsCanonicalizing(NivelCarrera::valores(), Carrera::distinct()->pluck('nivel')->push('posgrado')->unique()->all());
        $this->assertSame(EstadoInscripcion::valores(), CarreraEstudiante::ESTADOS);

        $admin = User::where('email', 'admin@uees.edu.ec')->first();
        $this->actingAs($admin)->post(route('administracion.carreras.store'), [
            'facultad_id' => Facultad::first()->id, 'codigo' => 'X1', 'nombre' => 'Prueba', 'nivel' => 'pregrado', 'modalidades' => [1],
        ])->assertSessionHasErrors('nivel');
    }

    public function test_c53_avisa_cuando_termina_una_importacion_en_cola(): void
    {
        Notification::fake();
        Storage::fake('local');
        Storage::disk('local')->put('importaciones/x/materias.csv', "codigo,nombre,creditos\nAV101,Aviso,3\n");
        $admin = User::where('email', 'admin@uees.edu.ec')->first();
        $importacion = Importacion::create(['entidad' => 'materias', 'archivo_ruta' => 'importaciones/x/materias.csv', 'user_id' => $admin->id, 'estado' => 'procesando']);

        (new ProcesarImportacion($importacion, avisar: true))->handle(app(ImportacionService::class));

        Notification::assertSentTo($admin, NotificacionPortal::class, function ($aviso) use ($admin, $importacion) {
            $datos = $aviso->toDatabase($admin);

            return str_contains($datos['titulo'] ?? json_encode($datos), "#{$importacion->id}")
                && str_contains(json_encode($datos), 'cargas');
        });

        Notification::fake();
        (new ProcesarImportacion($importacion->fresh()))->handle(app(ImportacionService::class));
        Notification::assertNothingSent();
    }

    public function test_la_campana_aparece_en_la_barra_superior(): void
    {
        $this->actingAs(User::where('email', 'admin@uees.edu.ec')->first())
            ->get(route('administracion.inicio'))
            ->assertOk()
            ->assertSee('data-campana-notificaciones', false);
    }
}

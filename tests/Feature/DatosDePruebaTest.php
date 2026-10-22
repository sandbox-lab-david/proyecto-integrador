<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\Facultad;
use App\Models\Malla;
use App\Models\User;
use App\Services\Academico\AutorizacionService;
use App\Services\Academico\CatalogoAcademicoService;
use App\Services\Academico\EstudianteService;
use App\Services\Solicitudes\CatalogoFacultades;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatosDePruebaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_estan_las_nueve_facultades_con_sus_carreras(): void
    {
        $this->assertSame(9, Facultad::count());
        $this->assertSame(38, Carrera::count());
    }

    public function test_cada_facultad_y_carrera_del_grupo_2_existe_con_el_mismo_nombre(): void
    {
        foreach (CatalogoFacultades::todas() as $facultad => $carreras) {
            $registro = Facultad::where('nombre', $facultad)->first();
            $this->assertNotNull($registro, "Falta la facultad \"$facultad\" de la lista del Grupo 2");

            foreach ($carreras as $carrera) {
                $this->assertTrue(
                    $registro->carreras()->where('nombre', $carrera)->exists(),
                    "Falta la carrera \"$carrera\" en \"$facultad\""
                );
            }
        }
    }

    public function test_medicina_y_enfermeria_no_admiten_tercer_registro(): void
    {
        $this->assertSame(
            ['Enfermería', 'Medicina'],
            app(CatalogoAcademicoService::class)->carrerasSinTercerRegistro()->pluck('nombre')->sort()->values()->all()
        );
    }

    public function test_hay_un_periodo_activo_y_vigente(): void
    {
        $this->travelTo('2026-10-21');
        $catalogo = app(CatalogoAcademicoService::class);

        $this->assertSame('ORD-2-2026', $catalogo->periodoActual()->codigo);
        $this->assertNotNull($catalogo->semanaActual());
    }

    public function test_los_contratos_devuelven_datos_reales(): void
    {
        $catalogo = app(CatalogoAcademicoService::class);
        $autorizacion = app(AutorizacionService::class);
        $computacion = Carrera::where('codigo', 'COMP')->first();
        $malla = Malla::where('carrera_id', $computacion->id)->first();

        $this->assertSame(['En línea', 'Presencial'], $catalogo->modalidadesDeCarrera($computacion->id)->pluck('nombre')->all());
        $this->assertCount(10, $catalogo->materiasDeMalla($malla->id));
        $this->assertCount(3, $catalogo->docentes());
        $this->assertSame('director@uees.edu.ec', $autorizacion->directorDeCarrera($computacion->id)->correo);
        $this->assertSame(
            ['asistente@uees.edu.ec'],
            $autorizacion->asistentesDeFacultad(Facultad::where('codigo', 'ING')->value('id'))->pluck('correo')->all()
        );
    }

    public function test_el_estudiante_de_prueba_tiene_su_inscripcion(): void
    {
        $estudiante = User::where('email', 'estudiante@uees.edu.ec')->first()->estudiante;
        $inscripcion = app(EstudianteService::class)->carrerasActivas($estudiante->id)->sole();

        $this->assertSame('Ingeniería en Computación', $inscripcion->carreraModalidad->carrera->nombre);
        $this->assertSame('Presencial', $inscripcion->carreraModalidad->modalidad->nombre);
        $this->assertSame('2024', $inscripcion->malla->version);
        $this->assertContains($inscripcion->estado, CarreraEstudiante::ESTADOS);
    }

    public function test_solo_quedan_los_usuarios_de_prueba(): void
    {
        $this->assertEqualsCanonicalizing(
            ['admin@uees.edu.ec', 'asistente@uees.edu.ec', 'director@uees.edu.ec', 'consulta@uees.edu.ec', 'estudiante@uees.edu.ec'],
            User::pluck('email')->all()
        );
    }

    public function test_cada_usuario_de_prueba_llega_a_su_lugar(): void
    {
        $destinos = [
            'admin@uees.edu.ec' => route('administracion.inicio'),
            'asistente@uees.edu.ec' => route('revision.asistente'),
            'director@uees.edu.ec' => route('revision.asistente'),
            'estudiante@uees.edu.ec' => route('estudiante.catalogo'),
        ];

        foreach ($destinos as $correo => $destino) {
            $this->actingAs(User::where('email', $correo)->first())->get('/dashboard')->assertRedirect($destino);
        }

        $this->actingAs(User::where('email', 'consulta@uees.edu.ec')->first())->get('/dashboard')->assertOk();
    }
}

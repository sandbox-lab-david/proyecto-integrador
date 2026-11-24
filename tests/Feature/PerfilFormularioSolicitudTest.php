<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\Estudiante;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Solicitudes\PerfilFormularioSolicitud;
use Database\Seeders\CatalogoInformativoSeeder;
use Database\Seeders\EstadosSolicitudSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilFormularioSolicitudTest extends TestCase
{
    use RefreshDatabase;

    public function test_precarga_el_perfil_de_g1_y_bloquea_solo_periodo_y_modalidad(): void
    {
        $usuario = User::factory()->create(['name' => 'Nombre de la cuenta', 'email' => 'cuenta@uees.edu.ec', 'tipo' => 'estudiante']);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id, 'codigo' => '0012345678', 'cedula' => '0912345678', 'celular' => '0991234567']);
        $inscripcion = CarreraEstudiante::factory()->create(['estudiante_id' => $estudiante->id]);
        CarreraEstudiante::factory()->create(); // Otra facultad, de otro estudiante: no se ofrece.
        $periodo = Periodo::factory()->activo()->create();

        $respuesta = $this->actingAs($usuario)->get(route('solicitudes.create', ['tramite' => 'retiro-carrera']))->assertOk();
        $datos = $respuesta->viewData('datosIniciales');
        $this->assertSame($estudiante->codigo, $datos['codigo']);
        $this->assertSame($estudiante->cedula, $datos['cedula']);
        $this->assertSame($estudiante->celular, $datos['celular']);
        $this->assertSame($periodo->codigo, $datos['periodo']);
        $this->assertSame($inscripcion->carreraModalidad->carrera->nombre, $datos['carrera']);
        $this->assertSame($inscripcion->carreraModalidad->carrera->facultad->nombre, $datos['facultad']);
        $this->assertSame($inscripcion->carreraModalidad->modalidad->nombre, $datos['modalidad']);
        $this->assertSame(['nombre' => $usuario->name, 'correo' => $usuario->email], $respuesta->viewData('datosCuenta'));
        $this->assertSame([$datos['facultad'] => [$datos['carrera']]], $respuesta->viewData('catalogoFacultades'));

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
        foreach (['codigo', 'cedula', 'celular'] as $campo) {
            $this->assertFalse($dom->getElementById($campo)->hasAttribute('readonly'), $campo);
        }
        foreach (['nombre', 'correo', 'modalidad', 'periodo'] as $campo) {
            $this->assertTrue($dom->getElementById($campo)->hasAttribute('readonly'), $campo);
        }
        $opciones = array_map(fn ($o) => $o->getAttribute('value'),
            iterator_to_array($dom->getElementById('facultad')->getElementsByTagName('option')));
        $this->assertSame(['', $datos['facultad']], $opciones);
        $this->assertSame('0012345678', $dom->getElementById('codigo')->getAttribute('value'));
        $this->assertSame('Nombre de la cuenta', $dom->getElementById('nombre')->getAttribute('value'));
        $respuesta->assertDontSee('en desarrollo');
    }

    public function test_sin_perfil_academico_mantiene_la_identidad_y_permite_completar_el_formulario(): void
    {
        $usuario = User::factory()->create(['tipo' => 'estudiante']);
        $respuesta = $this->actingAs($usuario)->get(route('solicitudes.create', ['tramite' => 'retiro-carrera']))->assertOk();
        $this->assertSame([], $respuesta->viewData('datosIniciales'));
        $this->assertSame([], $respuesta->viewData('modalidadesMatricula'));
        $this->assertSame($usuario->email, $respuesta->viewData('datosCuenta')['correo']);

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
        foreach (['modalidad', 'periodo'] as $campo) {
            $this->assertFalse($dom->getElementById($campo)->hasAttribute('readonly'), $campo);
        }
    }

    public function test_varias_carreras_no_eligen_una_inscripcion_arbitraria(): void
    {
        $usuario = User::factory()->create(['tipo' => 'estudiante']);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id]);
        $inscripciones = CarreraEstudiante::factory()->count(2)->create(['estudiante_id' => $estudiante->id]);
        $this->actingAs($usuario);
        $perfil = app(PerfilFormularioSolicitud::class);
        $datos = $perfil->iniciales();
        $this->assertSame($estudiante->codigo, $datos['codigo']);
        foreach (['facultad', 'carrera', 'modalidad'] as $campo) {
            $this->assertArrayNotHasKey($campo, $datos);
        }

        // La modalidad sale de la carrera que elija entre sus inscripciones.
        $segunda = $inscripciones[1]->carreraModalidad;
        $this->assertCount(2, $perfil->matriculas());
        $this->assertSame(['modalidad' => $segunda->modalidad->nombre],
            $perfil->fijos(['carrera' => $segunda->carrera->nombre, 'modalidad' => 'Escrita por el estudiante']));
    }

    public function test_el_servidor_impone_periodo_e_inscripcion_ante_un_post_manipulado(): void
    {
        $this->seed([CatalogoInformativoSeeder::class, EstadosSolicitudSeeder::class]);
        $usuario = User::factory()->create(['tipo' => 'estudiante']);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id]);
        $inscripcion = CarreraEstudiante::factory()->create(['estudiante_id' => $estudiante->id]);
        $periodo = Periodo::factory()->activo()->create();

        $this->actingAs($usuario)->putJson(route('borradores-temporales.update', 'gracia'), [
            'paso' => 1, 'facultad' => 'Facultad ajena', 'carrera' => 'Carrera ajena',
            'modalidad' => 'Distancia', 'periodo' => 'PER-INVENTADO',
            'celular' => '0990000000', 'materias' => [], 'campos' => [], 'requisitos' => [],
        ])->assertOk();

        $detalle = Solicitud::sole()->detalle;
        $this->assertSame($inscripcion->carreraModalidad->carrera->facultad->nombre, $detalle['facultad']);
        $this->assertSame($inscripcion->carreraModalidad->carrera->nombre, $detalle['carrera']);
        $this->assertSame($inscripcion->carreraModalidad->modalidad->nombre, $detalle['modalidad']);
        $this->assertSame($periodo->codigo, $detalle['periodo']);
    }

    public function test_con_varias_inscripciones_rechaza_una_carrera_que_no_cursa(): void
    {
        $usuario = User::factory()->create(['tipo' => 'estudiante']);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id]);
        CarreraEstudiante::factory()->count(2)->create(['estudiante_id' => $estudiante->id]);
        $ajena = CarreraEstudiante::factory()->create()->carreraModalidad->carrera;

        $this->actingAs($usuario)->putJson(route('borradores-temporales.update', 'gracia'), [
            'paso' => 1, 'facultad' => $ajena->facultad->nombre, 'carrera' => $ajena->nombre,
            'celular' => '0990000000', 'materias' => [], 'campos' => [], 'requisitos' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['facultad', 'carrera']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\Estudiante;
use App\Models\Periodo;
use App\Models\User;
use App\Services\Solicitudes\PerfilFormularioSolicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilFormularioSolicitudTest extends TestCase
{
    use RefreshDatabase;

    public function test_precarga_el_perfil_de_g1_y_mantiene_los_datos_editables(): void
    {
        $usuario = User::factory()->create(['name' => 'Nombre de la cuenta', 'email' => 'cuenta@uees.edu.ec', 'tipo' => 'estudiante']);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id, 'codigo' => '0012345678', 'cedula' => '0912345678', 'celular' => '0991234567']);
        $inscripcion = CarreraEstudiante::factory()->create(['estudiante_id' => $estudiante->id]);
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

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
        foreach (array_keys($datos) as $campo) {
            $this->assertFalse($dom->getElementById($campo)->hasAttribute('readonly'), $campo);
        }
        $this->assertSame('0012345678', $dom->getElementById('codigo')->getAttribute('value'));
        $this->assertSame('Nombre de la cuenta', $dom->getElementById('nombre')->getAttribute('value'));
        $respuesta->assertDontSee('en desarrollo');
    }

    public function test_sin_perfil_academico_mantiene_la_identidad_y_permite_completar_el_formulario(): void
    {
        $usuario = User::factory()->create(['tipo' => 'estudiante']);
        $respuesta = $this->actingAs($usuario)->get(route('solicitudes.create', ['tramite' => 'retiro-carrera']))->assertOk();
        $this->assertSame([], $respuesta->viewData('datosIniciales'));
        $this->assertSame($usuario->email, $respuesta->viewData('datosCuenta')['correo']);
    }

    public function test_varias_carreras_no_eligen_una_inscripcion_arbitraria(): void
    {
        $usuario = User::factory()->create(['tipo' => 'estudiante']);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id]);
        CarreraEstudiante::factory()->count(2)->create(['estudiante_id' => $estudiante->id]);
        $this->actingAs($usuario);
        $datos = app(PerfilFormularioSolicitud::class)->iniciales();
        $this->assertSame($estudiante->codigo, $datos['codigo']);
        foreach (['facultad', 'carrera', 'modalidad'] as $campo) {
            $this->assertArrayNotHasKey($campo, $datos);
        }
    }
}

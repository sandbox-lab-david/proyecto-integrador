<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\DirectorCarrera;
use App\Models\FacultadTrabajador;
use App\Models\Solicitud;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quién ve una solicitud además de su estudiante (SolicitudPolicy::view, C60):
 * el personal de su carrera o de su facultad, según los contratos C20 de G1.
 */
class SolicitudPolicyTest extends TestCase
{
    use LlavesAjenasPrueba, RefreshDatabase;

    private Carrera $carrera;

    private Solicitud $solicitud;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carrera = Carrera::factory()->create();
        $inscripcion = CarreraEstudiante::factory()->create([
            'carrera_modalidad_id' => CarreraModalidad::factory()->create(['carrera_id' => $this->carrera->id]),
        ]);
        $this->solicitud = Solicitud::factory()->enviada()->create(['carrera_estudiante_id' => $inscripcion->id] + $this->llavesAjenas());
    }

    private function director(Carrera $carrera): User
    {
        return DirectorCarrera::factory()->create([
            'carrera_id' => $carrera->id,
            'trabajador_id' => Trabajador::factory()->conAcceso()->conRol('director'),
        ])->trabajador->user;
    }

    private function asistente(int $facultadId): User
    {
        return FacultadTrabajador::factory()->create([
            'facultad_id' => $facultadId,
            'trabajador_id' => Trabajador::factory()->conAcceso()->conRol('asistente'),
        ])->trabajador->user;
    }

    public function test_el_estudiante_ve_la_suya(): void
    {
        $this->assertTrue($this->solicitud->carreraEstudiante->estudiante->user->can('view', $this->solicitud));
    }

    public function test_el_personal_ve_las_solicitudes_de_su_carrera_o_facultad(): void
    {
        $this->assertTrue($this->director($this->carrera)->can('view', $this->solicitud));
        $this->assertTrue($this->asistente($this->carrera->facultad_id)->can('view', $this->solicitud));
        $this->assertTrue($this->asistente($this->carrera->facultad_id)->can('descargar', $this->solicitud));
    }

    public function test_el_personal_de_otra_carrera_o_facultad_no_la_ve(): void
    {
        $otra = Carrera::factory()->create();

        $this->assertFalse($this->director($otra)->can('view', $this->solicitud));
        $this->assertFalse($this->asistente($otra->facultad_id)->can('view', $this->solicitud));
        $this->assertFalse(User::factory()->create(['tipo' => 'estudiante', 'activo' => true])->can('view', $this->solicitud));
    }

    public function test_el_personal_no_ve_un_borrador_ni_con_la_cuenta_inactiva(): void
    {
        $asistente = $this->asistente($this->carrera->facultad_id);

        $asistente->forceFill(['activo' => false])->save();
        $this->assertFalse($asistente->fresh()->can('view', $this->solicitud));

        $asistente->forceFill(['activo' => true])->save();
        $this->solicitud->forceFill(['enviada_at' => null])->save();
        $this->assertFalse($asistente->fresh()->can('view', $this->solicitud->fresh()));
        $this->assertFalse($this->director($this->carrera)->can('view', $this->solicitud->fresh()));
    }
}

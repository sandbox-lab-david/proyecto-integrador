<?php

namespace Tests\Feature;

use App\Models\Periodo;
use App\Models\Solicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Scopes publicados de Solicitud (contrato C42).
 *
 * Las pruebas marcadas como pendientes recorren tablas de otros grupos que
 * todavía no están en develop. Al activarlas, crear los datos con las
 * factories de cada dueño.
 */
class SolicitudScopesTest extends TestCase
{
    use LlavesAjenasPrueba, RefreshDatabase;

    private const FALTA_G1 = 'Pendiente: requiere los modelos y factories de G1 (CarreraEstudiante, CarreraModalidad, Carrera, Estudiante).';

    private const FALTA_G3 = 'Pendiente: requiere el modelo EstadoSolicitud y las constantes Estados de G3.';

    private function solicitud(array $atributos = []): Solicitud
    {
        return Solicitud::factory()->create($atributos + $this->llavesAjenas());
    }

    public function test_del_periodo_filtra_por_periodo(): void
    {
        [$periodo, $otro] = Periodo::factory()->count(2)->create()->modelKeys();
        $delPeriodo = $this->solicitud(['periodo_id' => $periodo]);
        $this->solicitud(['periodo_id' => $otro]);

        $this->assertEquals([$delPeriodo->id], Solicitud::delPeriodo($periodo)->pluck('id')->all());
    }

    public function test_del_tramite_filtra_por_tipo_de_tramite(): void
    {
        [$tramite, $otro] = [$this->tipoTramite('examen_gracia'), $this->tipoTramite('retiro_materia')];
        $delTramite = $this->solicitud(['tipo_tramite_id' => $tramite]);
        $this->solicitud(['tipo_tramite_id' => $otro]);

        $this->assertEquals([$delTramite->id], Solicitud::delTramite($tramite)->pluck('id')->all());
    }

    public function test_los_scopes_se_encadenan(): void
    {
        [$periodo, $otroPeriodo] = Periodo::factory()->count(2)->create()->modelKeys();
        [$tramite, $otroTramite] = [$this->tipoTramite('examen_gracia'), $this->tipoTramite('retiro_materia')];
        $coincide = $this->solicitud(['periodo_id' => $periodo, 'tipo_tramite_id' => $tramite]);
        $this->solicitud(['periodo_id' => $periodo, 'tipo_tramite_id' => $otroTramite]);
        $this->solicitud(['periodo_id' => $otroPeriodo, 'tipo_tramite_id' => $tramite]);

        $this->assertEquals([$coincide->id], Solicitud::delPeriodo($periodo)->delTramite($tramite)->pluck('id')->all());
    }

    public function test_buscar_sin_texto_no_filtra(): void
    {
        $this->solicitud();
        $this->solicitud();

        $this->assertSame(2, Solicitud::buscar(null)->count());
        $this->assertSame(2, Solicitud::buscar('')->count());
        $this->assertSame(2, Solicitud::buscar('   ')->count());
    }

    public function test_buscar_encuentra_por_codigo_de_solicitud_y_por_codigo_cafi(): void
    {
        // Aunque busca en columnas propias, la misma consulta recorre al estudiante.
        $this->markTestSkipped(self::FALTA_G1);
    }

    public function test_buscar_encuentra_por_nombres_apellidos_codigo_y_cedula_del_estudiante(): void
    {
        $this->markTestSkipped(self::FALTA_G1);
    }

    public function test_buscar_exige_todas_las_palabras_y_no_rompe_los_demas_filtros(): void
    {
        $this->markTestSkipped(self::FALTA_G1);
    }

    public function test_del_estudiante_trae_solo_las_solicitudes_de_ese_estudiante(): void
    {
        $this->markTestSkipped(self::FALTA_G1);
    }

    public function test_de_carrera_filtra_por_la_carrera_de_la_inscripcion(): void
    {
        $this->markTestSkipped(self::FALTA_G1);
    }

    public function test_de_facultad_filtra_por_la_facultad_de_la_carrera(): void
    {
        $this->markTestSkipped(self::FALTA_G1);
    }

    public function test_en_estado_acepta_un_codigo_o_varios(): void
    {
        $this->markTestSkipped(self::FALTA_G3);
    }
}

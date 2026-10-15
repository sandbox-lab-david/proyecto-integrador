<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * SolicitudService (contratos C43–C47).
 *
 * Las pruebas marcadas como pendientes necesitan modelos de otros grupos que
 * todavía no están en develop. Al activarlas, crear los datos con las
 * factories de cada dueño.
 */
class SolicitudServiceTest extends TestCase
{
    use RefreshDatabase;

    private const FALTA_TRABAJADOR = 'Pendiente: requiere el modelo Trabajador de G1.';

    private const FALTA_EXPEDIENTE = 'Pendiente: requiere HistorialSolicitud y Resolucion (G3) y EvaluacionElegibilidad (G4).';

    private const FALTA_ESTUDIANTE = 'Pendiente: requiere CarreraEstudiante (G1), TipoTramite (G4) y EstadoSolicitud (G3).';

    private function servicio(): SolicitudService
    {
        return app(SolicitudService::class);
    }

    private function solicitud(array $atributos = []): Solicitud
    {
        return Solicitud::factory()->create($atributos + [
            'carrera_estudiante_id' => 1,
            'tipo_tramite_id' => 1,
            'periodo_id' => 1,
            'estado_solicitud_id' => 1,
        ]);
    }

    public function test_detalle_falla_si_la_solicitud_no_existe(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->servicio()->detalle(999);
    }

    public function test_detalle_carga_materias_registros_respaldos_documentos_historial_evaluacion_y_resolucion(): void
    {
        $this->markTestSkipped(self::FALTA_EXPEDIENTE);
    }

    public function test_del_estudiante_devuelve_sus_solicitudes_de_la_mas_reciente_a_la_mas_antigua(): void
    {
        $this->markTestSkipped(self::FALTA_ESTUDIANTE);
    }

    public function test_verificar_materia_registra_quien_verifico_y_cuando(): void
    {
        $this->markTestSkipped(self::FALTA_TRABAJADOR);
    }

    public function test_verificar_materia_guarda_la_observacion_cuando_los_datos_no_coinciden(): void
    {
        $this->markTestSkipped(self::FALTA_TRABAJADOR);
    }

    public function test_verificar_materia_rechaza_una_observacion_de_mas_de_500_caracteres(): void
    {
        $this->markTestSkipped(self::FALTA_TRABAJADOR);
    }

    public function test_verificar_gpa_marca_y_desmarca_el_gpa_declarado(): void
    {
        $this->markTestSkipped(self::FALTA_TRABAJADOR);
    }

    public function test_registrar_codigo_sistema_guarda_el_numero_cafi(): void
    {
        $solicitud = $this->solicitud(['gpa_declarado' => 80]);

        $this->servicio()->registrarCodigoSistema($solicitud, '  CAFI-2026-0042 ');

        $solicitud = $solicitud->fresh();
        $this->assertSame('CAFI-2026-0042', $solicitud->codigo_sistema);
        $this->assertSame('80.00', $solicitud->gpa_declarado);
        $this->assertFalse($solicitud->gpa_verificado);
    }

    public function test_registrar_codigo_sistema_rechaza_un_codigo_vacio(): void
    {
        $solicitud = $this->solicitud();

        try {
            $this->servicio()->registrarCodigoSistema($solicitud, '   ');
            $this->fail('Debía rechazar el código vacío.');
        } catch (InvalidArgumentException) {
            $this->assertNull($solicitud->fresh()->codigo_sistema);
        }
    }

    public function test_registrar_codigo_sistema_rechaza_mas_de_30_caracteres(): void
    {
        $solicitud = $this->solicitud();

        try {
            $this->servicio()->registrarCodigoSistema($solicitud, str_repeat('9', 31));
            $this->fail('Debía rechazar un código de 31 caracteres.');
        } catch (InvalidArgumentException) {
            $this->assertNull($solicitud->fresh()->codigo_sistema);
        }

        $this->servicio()->registrarCodigoSistema($solicitud, str_repeat('9', 30));
        $this->assertSame(str_repeat('9', 30), $solicitud->fresh()->codigo_sistema);
    }

    public function test_siguiente_codigo_empieza_en_uno_cada_anio(): void
    {
        $this->assertSame('SOL-'.now()->year.'-00001', $this->servicio()->siguienteCodigo());

        $this->solicitud(['codigo' => 'SOL-2026-00007']);

        $this->assertSame('SOL-2027-00001', $this->servicio()->siguienteCodigo(2027));
    }

    public function test_siguiente_codigo_continua_despues_del_mayor_del_anio(): void
    {
        $this->solicitud(['codigo' => 'SOL-2026-00009']);
        $this->solicitud(['codigo' => 'SOL-2026-00010']);
        $this->solicitud(['codigo' => 'SOL-2025-00300']);

        $codigo = $this->servicio()->siguienteCodigo(2026);
        $this->assertSame('SOL-2026-00011', $codigo);

        $this->solicitud(['codigo' => $codigo]);
        $this->assertSame('SOL-2026-00012', $this->servicio()->siguienteCodigo(2026));
    }

    public function test_siguiente_codigo_sigue_contando_despues_de_99999(): void
    {
        $this->solicitud(['codigo' => 'SOL-2026-99999']);
        $this->assertSame('SOL-2026-100000', $this->servicio()->siguienteCodigo(2026));

        $this->solicitud(['codigo' => 'SOL-2026-100000']);
        $this->assertSame('SOL-2026-100001', $this->servicio()->siguienteCodigo(2026));
    }
}

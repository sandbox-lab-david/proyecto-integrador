<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\ComprobacionInformativa;
use App\Models\DocumentoFirmado;
use App\Models\DocumentoGenerado;
use App\Models\Estudiante;
use App\Models\HistorialSolicitud;
use App\Models\RegistroAnterior;
use App\Models\Resolucion;
use App\Models\Respaldo;
use App\Models\Solicitud;
use App\Models\SolicitudMateria;
use App\Models\Trabajador;
use App\Services\Solicitudes\SolicitudService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * SolicitudService (contratos C43–C47). Los datos de G1 salen de sus
 * factories; trámites y estados, del seeder del catálogo de G4.
 */
class SolicitudServiceTest extends TestCase
{
    use LlavesAjenasPrueba, RefreshDatabase;

    private function servicio(): SolicitudService
    {
        return app(SolicitudService::class);
    }

    private function solicitud(array $atributos = []): Solicitud
    {
        return Solicitud::factory()->create($atributos + $this->llavesAjenas());
    }

    private function materia(): SolicitudMateria
    {
        return SolicitudMateria::factory()->externa()->create(['solicitud_id' => $this->solicitud()->id]);
    }

    public function test_detalle_falla_si_la_solicitud_no_existe(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->servicio()->detalle(999);
    }

    public function test_detalle_carga_materias_registros_respaldos_documentos_historial_evaluacion_y_resolucion(): void
    {
        $solicitud = Solicitud::factory()->enviada()->create($this->llavesAjenas());
        $materias = SolicitudMateria::factory()->externa()->count(2)->create(['solicitud_id' => $solicitud->id]);
        RegistroAnterior::factory()->count(2)->create(['solicitud_materia_id' => $materias->first()->id]);
        Respaldo::factory()->create(['solicitud_id' => $solicitud->id]);
        DocumentoFirmado::factory()->create([
            'documento_generado_id' => DocumentoGenerado::factory()->create(['solicitud_id' => $solicitud->id]),
        ]);
        HistorialSolicitud::create(['solicitud_id' => $solicitud->id, 'estado_nuevo' => 'enviada', 'visible_estudiante' => true]);
        ComprobacionInformativa::create(['solicitud_id' => $solicitud->id, 'kb_version' => 'v3', 'respuesta' => ['resultado' => null]]);
        Resolucion::create(['solicitud_id' => $solicitud->id, 'numero_resolucion' => 'RES-2026-001',
            'fecha_resolucion' => '2026-10-15', 'decision' => 'Aprobado']);

        $detalle = $this->servicio()->detalle($solicitud->id);

        foreach (['materias', 'respaldos', 'documentosGenerados', 'historial', 'evaluacionesElegibilidad', 'resolucion'] as $relacion) {
            $this->assertTrue($detalle->relationLoaded($relacion), $relacion);
        }
        $this->assertCount(2, $detalle->materias);
        $this->assertTrue($detalle->materias->every->relationLoaded('registrosAnteriores'));
        $this->assertSame(2, $detalle->materias->sum(fn ($materia) => $materia->registrosAnteriores->count()));
        $this->assertCount(1, $detalle->respaldos);
        $this->assertTrue($detalle->documentosGenerados->first()->relationLoaded('documentoFirmado'));
        $this->assertNotNull($detalle->documentosGenerados->first()->documentoFirmado);
        $this->assertCount(1, $detalle->historial);
        $this->assertCount(1, $detalle->evaluacionesElegibilidad);
        $this->assertSame('RES-2026-001', $detalle->resolucion->numero_resolucion);
    }

    public function test_del_estudiante_devuelve_sus_solicitudes_de_la_mas_reciente_a_la_mas_antigua(): void
    {
        $estudiante = Estudiante::factory()->create();
        $inscripcion = ['carrera_estudiante_id' => CarreraEstudiante::factory()->create(['estudiante_id' => $estudiante->id])->id];
        $primera = Solicitud::factory()->enviada()->create($inscripcion + $this->llavesAjenas());
        $segunda = Solicitud::factory()->enviada()->create($inscripcion + $this->llavesAjenas());
        // Ni su borrador ni la solicitud de otro estudiante.
        $this->solicitud($inscripcion + ['estado_solicitud_id' => $this->estadoSolicitud('borrador')]);
        Solicitud::factory()->enviada()->create($this->llavesAjenas());

        $suyas = $this->servicio()->delEstudiante($estudiante->id);

        $this->assertEquals([$segunda->id, $primera->id], $suyas->modelKeys());
        $this->assertTrue($suyas->every->relationLoaded('tipoTramite'));
        $this->assertSame('enviada', $suyas->first()->estadoSolicitud->codigo);
    }

    public function test_verificar_materia_registra_quien_verifico_y_cuando(): void
    {
        $materia = $this->materia();
        $asistente = Trabajador::factory()->create();
        $this->travelTo('2026-10-20 10:30:00');

        $this->servicio()->verificarMateria($materia, $asistente, true, null);

        $materia = $materia->fresh();
        $this->assertTrue($materia->verificado);
        $this->assertSame($asistente->id, $materia->verificado_por);
        $this->assertSame($asistente->id, $materia->verificador->id);
        $this->assertSame('2026-10-20 10:30:00', $materia->verificado_at->toDateTimeString());
        $this->assertNull($materia->observacion_verificacion);
    }

    public function test_verificar_materia_guarda_la_observacion_cuando_los_datos_no_coinciden(): void
    {
        $materia = $this->materia();

        $this->servicio()->verificarMateria($materia, Trabajador::factory()->create(), false, 'La nota en el acta es 52, no 58.');

        $materia = $materia->fresh();
        $this->assertFalse($materia->verificado);
        $this->assertSame('La nota en el acta es 52, no 58.', $materia->observacion_verificacion);
        $this->assertNotNull($materia->verificado_at);
    }

    public function test_verificar_materia_rechaza_una_observacion_de_mas_de_500_caracteres(): void
    {
        $materia = $this->materia();
        $asistente = Trabajador::factory()->create();

        try {
            $this->servicio()->verificarMateria($materia, $asistente, false, str_repeat('á', 501));
            $this->fail('Debía rechazar 501 caracteres.');
        } catch (InvalidArgumentException) {
            $this->assertNull($materia->fresh()->verificado_por);
        }

        // El límite cuenta caracteres, no bytes.
        $this->servicio()->verificarMateria($materia, $asistente, false, str_repeat('á', 500));
        $this->assertSame(500, mb_strlen($materia->fresh()->observacion_verificacion));
    }

    public function test_verificar_gpa_marca_y_desmarca_el_gpa_declarado(): void
    {
        $solicitud = $this->solicitud(['gpa_declarado' => 81.5]);
        $asistente = Trabajador::factory()->create();

        $this->servicio()->verificarGpa($solicitud, $asistente, true);
        $this->assertTrue($solicitud->fresh()->gpa_verificado);

        $this->servicio()->verificarGpa($solicitud, $asistente, false);
        $this->assertFalse($solicitud->fresh()->gpa_verificado);
        $this->assertSame('81.50', $solicitud->fresh()->gpa_declarado);
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

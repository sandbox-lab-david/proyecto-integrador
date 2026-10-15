<?php

namespace Tests\Feature;

use App\Services\Solicitudes\SeguimientoSolicitudes;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * «Mis solicitudes» (RF-2.13).
 *
 * Hoy la página lista lo que la sesión tiene sin terminar. El listado de las
 * solicitudes guardadas y su seguimiento están escritos contra los contratos
 * de G1 y G3; esas pruebas quedan pendientes hasta que sus clases estén en
 * develop. Al activarlas, crear los datos con las factories de cada dueño.
 */
class MisSolicitudesTest extends TestCase
{
    use ArchivosDePrueba;

    private const FALTA_SEGUIMIENTO = 'Pendiente: requiere EstudianteService y AutorizacionService (G1), EstadoSolicitudService, <x-estado-badge> y <x-linea-tiempo> (G3) y TipoTramite (G4).';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function guardarBorrador(string $tramite = 'recuperacion', int $paso = 2): void
    {
        $this->putJson(route('borradores-temporales.update', $tramite), [
            'paso' => $paso,
            'celular' => '0990000000',
            'materias' => [],
            'campos' => [],
            'requisitos' => [],
        ])->assertOk();
    }

    public function test_sin_nada_empezado_invita_a_ir_al_catalogo(): void
    {
        $this->get(route('solicitudes.index'))
            ->assertOk()
            ->assertSee('Mis solicitudes')
            ->assertSee('No tienes solicitudes a medias.')
            ->assertSee(route('estudiante.catalogo'), false)
            ->assertDontSee('data-descartar="', false);
    }

    public function test_lista_lo_que_la_sesion_dejo_sin_terminar(): void
    {
        $this->guardarBorrador('recuperacion', 3);

        $this->get(route('solicitudes.index'))
            ->assertOk()
            ->assertSee('Examen de recuperación')
            ->assertSee('Paso 3 de 4')
            ->assertSee(route('solicitudes.create', ['tramite' => 'recuperacion']), false)
            ->assertSee(route('borradores-temporales.destroy', 'recuperacion'), false)
            ->assertDontSee('No tienes solicitudes a medias.');
    }

    public function test_muestra_primero_la_guardada_mas_recientemente(): void
    {
        $this->travelTo(now()->subHour(), fn () => $this->guardarBorrador('recuperacion'));
        $this->guardarBorrador('gracia');

        $this->assertSame(
            ['gracia', 'recuperacion'],
            array_column($this->get(route('solicitudes.index'))->viewData('sinTerminar'), 'codigo')
        );
    }

    public function test_indica_los_respaldos_y_el_pdf_firmado_de_cada_una(): void
    {
        $this->guardarBorrador('recuperacion', 3);
        $this->guardarBorrador('gracia');

        foreach (['uno.pdf', 'dos.pdf'] as $nombre) {
            $this->postJson(route('respaldos-temporales.store'), [
                'tramite' => 'recuperacion',
                'archivo' => $this->pdf($nombre),
            ])->assertCreated();
        }

        $this->postJson(route('firmados-temporales.store', 'recuperacion'), [
            'archivo' => $this->pdf('Solicitud firmada.pdf'),
        ])->assertCreated();

        $respuesta = $this->get(route('solicitudes.index'))
            ->assertOk()
            ->assertSee('2 respaldos adjuntos')
            ->assertSee('PDF firmado: Solicitud firmada.pdf')
            // La ruta donde quedó guardado nunca llega a la página.
            ->assertDontSee('respaldos-temporales/', false);

        $porTramite = array_column($respuesta->viewData('sinTerminar'), null, 'codigo');

        $this->assertSame(2, $porTramite['recuperacion']['respaldos']);
        $this->assertSame('Solicitud firmada.pdf', $porTramite['recuperacion']['firmado']);
        $this->assertSame(0, $porTramite['gracia']['respaldos']);
        $this->assertNull($porTramite['gracia']['firmado']);
    }

    public function test_descartar_la_quita_de_la_lista(): void
    {
        $this->guardarBorrador('recuperacion');
        $this->guardarBorrador('gracia');

        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->get(route('solicitudes.index'))
            ->assertOk()
            ->assertSee('Examen de gracia')
            ->assertDontSee('Examen de recuperación');
    }

    public function test_otra_sesion_no_ve_las_solicitudes_sin_terminar(): void
    {
        $this->guardarBorrador();

        $this->flushSession();

        $this->get(route('solicitudes.index'))
            ->assertOk()
            ->assertSee('No tienes solicitudes a medias.')
            ->assertDontSee('Examen de recuperación');
    }

    public function test_el_catalogo_enlaza_a_mis_solicitudes(): void
    {
        $this->get(route('estudiante.catalogo'))
            ->assertOk()
            ->assertSee(route('solicitudes.index'), false);
    }

    public function test_mientras_falten_los_servicios_de_otros_grupos_avisa_que_no_hay_seguimiento(): void
    {
        if (SeguimientoSolicitudes::disponible()) {
            $this->markTestSkipped('Los servicios de G1 y G3 ya existen: esta prueba ya no aplica.');
        }

        $respuesta = $this->get(route('solicitudes.index'))
            ->assertOk()
            ->assertSee('El seguimiento todavía no está disponible.');

        $this->assertNull($respuesta->viewData('solicitudes'));

        $this->get(route('solicitudes.show', 1))->assertNotFound();
    }

    public function test_el_seguimiento_solo_acepta_un_identificador_numerico(): void
    {
        $this->get('/estudiante/solicitudes/SOL-2026-00001')->assertNotFound();

        // «nueva» sigue siendo el formulario, no una solicitud.
        $this->get('/estudiante/solicitudes/nueva?tramite=recuperacion')->assertOk();
    }

    public function test_lista_las_solicitudes_del_estudiante_con_su_estado(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }

    public function test_no_lista_solicitudes_de_otro_estudiante(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }

    public function test_el_seguimiento_muestra_estado_linea_de_tiempo_observaciones_y_resolucion(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }

    public function test_el_seguimiento_nunca_muestra_las_observaciones_internas(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }

    public function test_el_seguimiento_sin_resolucion_no_muestra_ese_bloque(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }

    public function test_un_estudiante_no_puede_abrir_la_solicitud_de_otro(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }

    public function test_el_personal_ve_las_solicitudes_de_su_carrera_o_facultad(): void
    {
        $this->markTestSkipped(self::FALTA_SEGUIMIENTO);
    }
}

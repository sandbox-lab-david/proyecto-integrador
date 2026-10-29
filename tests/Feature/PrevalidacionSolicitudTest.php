<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Chatbot\Prolog\PrologException;
use App\Services\Solicitudes\PrevalidacionSolicitud;
use App\Services\Solicitudes\RequisitosSolicitud;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Pre-validación informativa del paso 4 (RF-2.8) con el motor de G4 simulado por su interfaz. */
class PrevalidacionSolicitudTest extends TestCase
{
    use ArchivosDePrueba, LlavesAjenasPrueba, RefreshDatabase, SesionEstudiantePrueba;

    /** @var array<int, array> Entradas que recibió el motor. */
    private array $entradas = [];

    private function motor(?\Throwable $fallo = null): void
    {
        $motor = $this->createMock(PrologClient::class);
        $motor->method('evaluar')->willReturnCallback(function (array $entrada) use ($fallo) {
            $this->entradas[] = $entrada;
            if ($fallo) {
                throw $fallo;
            }

            return ['version' => 3, 'kb_version' => 'prueba', 'tramite_id' => $entrada['tramite_id'],
                'intencion' => $entrada['intencion'], 'documentos' => [], 'costo' => null, 'plazo' => null, 'resultado' => null,
                'items' => $entrada['tramite_id'] === 'examen_recuperacion' ? [] : ['La facultad revisa los antecedentes de tu solicitud.'],
                'comprobaciones' => $entrada['tramite_id'] === 'examen_recuperacion'
                    ? [['codigo' => 'gpa_general', 'texto' => 'La referencia de GPA general es 75.', 'datos' => []]] : [],
                'aviso' => 'Aviso de prueba del motor'];
        });
        $this->app->instance(PrologClient::class, $motor);
    }

    private function consultar(string $tramite, array $campos, ?array $requisitos = null)
    {
        return $this->postJson(route('solicitudes.prevalidacion'), ['tramite' => $tramite, 'campos' => $campos]
            + ($requisitos === null ? [] : ['requisitos' => $requisitos]));
    }

    public function test_muestra_las_comprobaciones_con_los_datos_declarados(): void
    {
        $this->motor();

        $this->consultar('recuperacion', ['gpa_general' => '72.5', 'historial_recuperacion' => 'con_fecha',
            'ultima_recuperacion' => '2025-10-20', 'observacion' => 'Texto que no viaja al motor'])
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('La referencia de GPA general es 75.')
            ->assertSee('Aviso de prueba del motor')
            ->assertDontSee('Volver a consultar');

        $this->assertCount(1, $this->entradas);
        $this->assertSame('examen_recuperacion', $this->entradas[0]['tramite_id']);
        $this->assertSame('comprobar_referencias', $this->entradas[0]['intencion']);
        $this->assertSame(['gpa_general' => 72.5, 'fecha_ultima_recuperacion' => '2025-10-20'], $this->entradas[0]['perfil']);
    }

    public function test_la_fecha_oculta_por_su_condicion_no_viaja_al_motor(): void
    {
        $this->motor();

        $this->consultar('recuperacion', ['gpa_general' => '', 'historial_recuperacion' => 'nunca',
            'ultima_recuperacion' => '2025-10-20'])->assertOk();

        $this->assertSame([], $this->entradas[0]['perfil']);
    }

    public function test_un_tramite_sin_comprobaciones_no_envia_datos_personales(): void
    {
        $this->motor();

        $this->consultar('ayudante', ['gpa_general' => '90'])
            ->assertOk()
            ->assertSee('La facultad revisa los antecedentes de tu solicitud.');

        $this->assertSame('ayudante_catedra', $this->entradas[0]['tramite_id']);
        $this->assertSame([], $this->entradas[0]['perfil']);
    }

    public function test_admite_un_tramite_sin_datos_declarados(): void
    {
        $this->motor();

        $this->consultar('gracia', [])
            ->assertOk()
            ->assertSee('La facultad revisa los antecedentes de tu solicitud.');

        $this->assertSame('examen_gracia', $this->entradas[0]['tramite_id']);
        $this->assertSame([], $this->entradas[0]['perfil']);
    }

    public function test_envia_los_documentos_de_los_respaldos_subidos(): void
    {
        $this->motor();
        $this->discoTemporal();
        $cedula = $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'ayudante', 'archivo' => $this->pdf()])->json('id');
        $record = $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'ayudante', 'archivo' => $this->png()])->json('id');
        $otroTramite = $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'gracia', 'archivo' => $this->pdf()])->json('id');

        $this->consultar('ayudante', [], [
            $cedula => 'copia_cedula', $record => 'record_academico', $otroTramite => 'certificado_medico',
            'archivo-inexistente' => 'solicitud_firmada',
        ])->assertOk();

        $this->assertSame(['documentos_subidos' => ['copia_cedula', 'record_academico']], $this->entradas[0]['perfil']);
    }

    public function test_sin_respaldos_comprueba_los_documentos_con_la_lista_vacia(): void
    {
        $this->motor();

        $this->consultar('recuperacion', ['gpa_general' => '80'], [])->assertOk();

        $this->assertSame(['gpa_general' => 80.0, 'documentos_subidos' => []], $this->entradas[0]['perfil']);
    }

    public function test_si_prolog_falla_lo_informa_y_permite_reintentar(): void
    {
        $this->motor(PrologException::noDisponible());

        $this->consultar('recuperacion', ['gpa_general' => '80'])
            ->assertOk()
            ->assertSee('No se pudo hacer la comprobación en este momento.')
            ->assertSee('Volver a consultar');
    }

    public function test_valida_los_datos_antes_de_consultar(): void
    {
        $this->motor();

        $this->consultar('recuperacion', ['gpa_general' => '150'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['campos.gpa_general' => 'menor o igual que 100']);
        $this->consultar('recuperacion', ['historial_recuperacion' => 'con_fecha',
            'ultima_recuperacion' => now('America/Guayaquil')->addDay()->toDateString()])
            ->assertJsonValidationErrors(['campos.ultima_recuperacion' => 'fecha futura']);
        $this->consultar('tramite-inexistente', [])->assertNotFound();

        $this->assertSame([], $this->entradas);
    }

    public function test_requiere_sesion_de_estudiante(): void
    {
        $this->motor();
        $this->app['auth']->forgetGuards();

        $this->post(route('solicitudes.prevalidacion'), ['tramite' => 'recuperacion', 'campos' => []])
            ->assertRedirect(route('login'));
        $this->assertSame([], $this->entradas);
    }

    public function test_el_paso_4_incluye_la_comprobacion_previa(): void
    {
        $this->partialMock(RequisitosSolicitud::class)
            ->shouldReceive('consultar')->andReturn(['disponible' => true, 'items' => [], 'documentos' => [], 'aviso' => null, 'kb_version' => 'prueba']);

        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))
            ->assertOk()
            ->assertSee('Comprobación previa')
            ->assertSee('id="resultado-prevalidacion"', false)
            ->assertSee(json_encode(route('solicitudes.prevalidacion')), false);
    }

    public function test_registra_la_comprobacion_junto_al_expediente_enviado(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $this->motor();
        $solicitud = Solicitud::factory()->create([
            ...$this->llavesAjenas(),
            'gpa_declarado' => 72, 'fecha_ultima_recuperacion' => '2025-10-20',
        ]);

        app(PrevalidacionSolicitud::class)->registrar($solicitud);

        $this->assertSame(['gpa_general' => 72.0, 'fecha_ultima_recuperacion' => '2025-10-20'], $this->entradas[0]['perfil']);
        $comprobacion = $solicitud->evaluacionesElegibilidad()->sole();
        $this->assertSame('prueba', $comprobacion->kb_version);
        $this->assertSame('gpa_general', $comprobacion->respuesta['comprobaciones'][0]['codigo']);
    }

    public function test_un_fallo_al_registrar_no_interrumpe_el_envio(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $this->motor(PrologException::noDisponible());
        $solicitud = Solicitud::factory()->create([
            ...$this->llavesAjenas(),
        ]);

        app(PrevalidacionSolicitud::class)->registrar($solicitud);

        $this->assertCount(1, $this->entradas);
        $this->assertDatabaseCount('comprobaciones_informativas', 0);
    }
}

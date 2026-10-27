<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\EnvioSolicitud;
use App\Services\Solicitudes\IntegracionEnvio;
use App\Services\Solicitudes\PrevalidacionSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnvioSolicitudTest extends TestCase
{
    use ArchivosDePrueba, LlavesAjenasPrueba, RefreshDatabase, RequisitosPrologPrueba;

    /** Contexto que devuelve la integración: filas reales del catálogo y de G1. */
    private ?array $contexto = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function integrar(bool $fallar = false): void
    {
        $this->contexto ??= ['estado_solicitud_id' => $this->estadoSolicitud('borrador')]
            + $this->llavesAjenas() + ['requisitos' => ['respaldo' => $this->documentoRequerido('examen_gracia')]];
        $this->app->instance(IntegracionEnvio::class, new class($fallar, $this->contexto, $this->estadoSolicitud('enviada')) implements IntegracionEnvio
        {
            public int $envios = 0;

            public function __construct(private bool $fallar, private array $contexto, private int $enviada) {}

            public function resolver(User $usuario, array $datos): array
            {
                return $this->contexto;
            }

            public function contextoBorrador(User $usuario, string $tramite, array $datos): ?array
            {
                return null;
            }

            public function enviar(Solicitud $solicitud, User $usuario): void
            {
                $this->envios++;
                if ($this->fallar) {
                    throw new \RuntimeException('Error de transición');
                }
                $solicitud->forceFill(['estado_solicitud_id' => $this->enviada])->save();
            }
        });
    }

    private function preparar(array $campos = []): array
    {
        $datos = ['tramite' => 'gracia', 'nombre' => 'Ana Pérez', 'codigo' => '20260001',
            'carrera' => 'Ingeniería en Computación', 'correo' => 'ana@uees.edu.ec', 'celular' => '0991234567',
            'cedula' => '0912345678', 'facultad' => 'Ingeniería', 'modalidad' => 'Presencial', 'periodo' => '2026-I',
            'materias' => [app(TramitesSimulados::class)->materias()[0] + ['datos' => ['nota' => '70', 'asistencia' => '80']]],
            'campos' => $campos + ['motivo' => 'Solicito rendir el examen.'], 'declaracion' => true];
        app(DocumentosTemporales::class)->guardar('gracia', 'Word de prueba', $datos);
        $datos['clave_envio'] = app(DocumentosTemporales::class)->buscar('gracia')['version'];
        app(FirmadosTemporales::class)->guardar($this->pdf(), 'gracia');
        $respaldo = app(RespaldosTemporales::class)->agregar($this->pdf(), 'gracia');
        $datos['requisitos'] = [$respaldo['id'] => 'respaldo'];

        return $datos;
    }

    public function test_guarda_el_expediente_y_sus_archivos_definitivos(): void
    {
        $this->integrar();
        $datos = $this->preparar();
        $usuario = User::factory()->create();
        $solicitud = app(EnvioSolicitud::class)->enviar($usuario, $datos);
        $this->assertMatchesRegularExpression('/^SOL-\d{4}-00001$/', $solicitud->codigo);
        $this->assertSame($this->estadoSolicitud('enviada'), $solicitud->estado_solicitud_id);
        $this->assertNotNull($solicitud->enviada_at);
        $this->assertDatabaseCount('solicitud_materia', 1);
        $this->assertNull($solicitud->materias()->firstOrFail()->curso_id);
        $this->assertSame($datos['materias'], $solicitud->detalle['materias']);
        $this->assertSame('0912345678', $solicitud->detalle['cedula']);
        $this->assertDatabaseCount('respaldos', 1);
        $generado = $solicitud->documentosGenerados()->firstOrFail();
        Storage::disk('local')->assertExists($generado->ruta_docx);
        Storage::disk('local')->assertExists($generado->documentoFirmado->ruta);
        $this->assertSame($usuario->id, $generado->documentoFirmado->subido_por);
        $this->assertNull(app(DocumentosTemporales::class)->buscar('gracia'));
        $this->assertNull(app(FirmadosTemporales::class)->buscar('gracia'));
    }

    #[DataProvider('valoresGpa')]
    public function test_conserva_gpa_cero_y_distingue_el_campo_vacio(array $campos, ?string $esperado): void
    {
        $this->integrar();
        $solicitud = app(EnvioSolicitud::class)->enviar(User::factory()->create(), $this->preparar($campos));
        $this->assertSame($esperado, $solicitud->fresh()->gpa_declarado);
    }

    public static function valoresGpa(): array
    {
        return [
            'cero general' => [['gpa_general' => '0'], '0.00'],
            'vacio' => [['gpa_general' => ''], null],
        ];
    }

    public function test_un_error_revierte_registros_y_conserva_temporales(): void
    {
        $this->integrar(true);
        $datos = $this->preparar();
        try {
            app(EnvioSolicitud::class)->enviar(User::factory()->create(), $datos);
            $this->fail('Debe fallar la transición.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Error de transición', $error->getMessage());
        }
        $this->assertDatabaseCount('solicitudes', 0);
        $this->assertDatabaseCount('respaldos', 0);
        $this->assertDatabaseCount('documentos_generados', 0);
        $this->assertDatabaseCount('documentos_firmados', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('expedientes'));
        Storage::disk('local')->assertExists(app(DocumentosTemporales::class)->buscar('gracia')['ruta']);
        Storage::disk('local')->assertExists(app(FirmadosTemporales::class)->buscar('gracia')['ruta']);
    }

    public function test_rechaza_firma_de_datos_que_cambiaron(): void
    {
        $this->integrar();
        $datos = $this->preparar();
        $datos['campos']['motivo'] = 'Otro motivo';
        $this->expectException(ValidationException::class);
        app(EnvioSolicitud::class)->enviar(User::factory()->create(), $datos);
    }

    public function test_envio_requiere_sesion_y_muestra_acceso_al_login(): void
    {
        $datos = $this->preparar();
        $this->app['auth']->forgetGuards();
        $this->postJson(route('solicitudes.enviar'), $datos)->assertStatus(401);
        $this->assertDatabaseCount('solicitudes', 0);
        $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->assertRedirect(route('login'));
    }

    public function test_repetir_el_envio_devuelve_el_mismo_expediente_aunque_no_queden_temporales(): void
    {
        $this->integrar();
        $datos = $this->preparar();
        $usuario = User::factory()->create();
        $servicio = app(EnvioSolicitud::class);
        $primera = $servicio->enviar($usuario, $datos);
        $archivos = Storage::disk('local')->allFiles('expedientes');
        $segunda = $servicio->enviar($usuario, $datos);
        $this->assertSame($primera->id, $segunda->id);
        $this->assertSame($primera->codigo, $segunda->codigo);
        $this->assertDatabaseCount('solicitudes', 1);
        $this->assertDatabaseCount('documentos_generados', 1);
        $this->assertDatabaseCount('documentos_firmados', 1);
        $this->assertSame($archivos, Storage::disk('local')->allFiles('expedientes'));
        $this->assertSame(1, app(IntegracionEnvio::class)->envios);
    }

    public function test_registra_la_comprobacion_informativa_una_vez_por_envio(): void
    {
        $this->integrar();
        $datos = $this->preparar();
        $this->partialMock(PrevalidacionSolicitud::class)->shouldReceive('registrar')->once()
            ->with(\Mockery::on(fn ($solicitud) => $solicitud instanceof Solicitud && $solicitud->exists));
        $usuario = User::factory()->create();
        $servicio = app(EnvioSolicitud::class);

        $servicio->enviar($usuario, $datos);
        $servicio->enviar($usuario, $datos);
    }

    public function test_un_envio_fallido_no_registra_comprobacion(): void
    {
        $this->integrar(fallar: true);
        $datos = $this->preparar();
        $this->partialMock(PrevalidacionSolicitud::class)->shouldReceive('registrar')->never();

        $this->expectException(\RuntimeException::class);
        app(EnvioSolicitud::class)->enviar(User::factory()->create(), $datos);
    }

    public function test_no_reutiliza_una_clave_con_datos_distintos(): void
    {
        $this->integrar();
        $datos = $this->preparar();
        $usuario = User::factory()->create();
        app(EnvioSolicitud::class)->enviar($usuario, $datos);
        $datos['campos']['motivo'] = 'Un trámite distinto';
        try {
            app(EnvioSolicitud::class)->enviar($usuario, $datos);
            $this->fail('No debe aceptar otros datos con la misma clave.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('clave_envio', $error->errors());
        }
        $this->assertDatabaseCount('solicitudes', 1);
    }

    public function test_admite_dos_solicitudes_nuevas_del_mismo_tipo_para_el_mismo_usuario(): void
    {
        $this->integrar();
        $usuario = User::factory()->create();
        $primera = app(EnvioSolicitud::class)->enviar($usuario, $this->preparar());
        $segunda = app(EnvioSolicitud::class)->enviar($usuario, $this->preparar());
        $this->assertNotSame($primera->id, $segunda->id);
        $this->assertNotSame($primera->codigo, $segunda->codigo);
        $this->assertDatabaseCount('solicitudes', 2);
        $this->assertSame(2, app(IntegracionEnvio::class)->envios);
    }

    public function test_un_envio_fallido_se_puede_reintentar_con_su_clave_original(): void
    {
        $this->integrar(true);
        $usuario = User::factory()->create();
        $datos = $this->preparar();
        try {
            app(EnvioSolicitud::class)->enviar($usuario, $datos);
            $this->fail('La transición de prueba debe fallar.');
        } catch (\RuntimeException) {
            $this->assertDatabaseCount('solicitudes', 0);
        }
        $this->integrar();
        $solicitud = app(EnvioSolicitud::class)->enviar($usuario, $datos);
        $this->assertNotNull($solicitud->enviada_at);
        $this->assertDatabaseCount('solicitudes', 1);
    }

    public function test_otro_usuario_no_recupera_un_expediente_usando_una_clave_ajena(): void
    {
        $this->integrar();
        $datos = $this->preparar();
        $propietario = User::factory()->create();
        app(EnvioSolicitud::class)->enviar($propietario, $datos);
        $this->flushSession();
        try {
            app(EnvioSolicitud::class)->enviar(User::factory()->create(), $datos);
            $this->fail('La clave no concede acceso a otra cuenta.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('clave_envio', $error->errors());
        }
        $this->assertDatabaseCount('solicitudes', 1);
        $this->assertSame($propietario->id, Solicitud::firstOrFail()->detalle['envio_usuario_id']);
    }

    public function test_reintento_http_tras_perder_la_respuesta_devuelve_el_mismo_codigo(): void
    {
        $this->integrar();
        $this->actingAs(User::factory()->create(['name' => 'Ana Pérez', 'email' => 'ana@uees.edu.ec']));
        $datos = $this->preparar();
        $primera = $this->postJson(route('solicitudes.enviar'), $datos)->assertCreated()->json();
        $segunda = $this->postJson(route('solicitudes.enviar'), $datos)->assertCreated()->json();
        $this->assertSame($primera, $segunda);
        $this->assertDatabaseCount('solicitudes', 1);
    }

    public function test_una_clave_inventada_no_genera_otro_expediente(): void
    {
        $this->integrar();
        $usuario = User::factory()->create(['name' => 'Ana Pérez', 'email' => 'ana@uees.edu.ec']);
        $this->actingAs($usuario);
        $datos = $this->preparar();
        $datos['clave_envio'] = (string) Str::uuid();
        $this->postJson(route('solicitudes.enviar'), $datos)->assertUnprocessable()->assertJsonValidationErrors('clave_envio');
        unset($datos['clave_envio']);
        $this->postJson(route('solicitudes.enviar'), $datos)->assertUnprocessable()->assertJsonValidationErrors('clave_envio');
        $this->assertDatabaseCount('solicitudes', 0);
    }
}

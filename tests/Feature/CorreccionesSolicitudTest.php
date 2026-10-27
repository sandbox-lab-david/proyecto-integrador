<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\Curso;
use App\Models\EstadoSolicitud;
use App\Models\Estudiante;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\BorradoresTemporales;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\EnvioSolicitud;
use App\Services\Solicitudes\IntegracionEnvio;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorreccionesSolicitudTest extends TestCase
{
    use FacultadesG1Prueba, LlavesAjenasPrueba;
    use MateriasCatalogoPrueba, ArchivosDePrueba;

    private User $usuario;

    private int $inscripcion;

    private int $ajena;

    private int $observada;

    private int $enviada;

    /** @var array{tipo_tramite_id: int, periodo_id: int, curso_id: int, documento_requerido_id: int} */
    private array $llaves;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discoTemporal();
        // Filas reales de G1 (factories) y del catálogo de G4 (seeder), con las migraciones de cada dueño.
        $this->usuario = User::factory()->create(['tipo' => 'estudiante', 'activo' => true]);
        $this->inscripcion = CarreraEstudiante::factory()->create([
            'estudiante_id' => Estudiante::factory()->create(['user_id' => $this->usuario->id]),
        ])->id;
        $this->ajena = CarreraEstudiante::factory()->create()->id;
        $this->observada = EstadoSolicitud::create(['codigo' => 'observado', 'nombre' => 'Observado'])->id;
        $this->enviada = $this->estadoSolicitud('enviada');
        $this->llaves = [
            'tipo_tramite_id' => $this->tipoTramite('examen_gracia'),
            'periodo_id' => Periodo::factory()->create()->id,
            'curso_id' => Curso::factory()->create()->id,
            'documento_requerido_id' => $this->documentoRequerido('examen_gracia'),
        ];
        $this->actingAs($this->usuario);
    }

    private function solicitud(?int $inscripcion = null): Solicitud
    {
        $solicitud = Solicitud::create([
            'codigo' => 'SOL-2026-'.random_int(10000, 99999), 'carrera_estudiante_id' => $inscripcion ?? $this->inscripcion,
            'tipo_tramite_id' => $this->llaves['tipo_tramite_id'], 'periodo_id' => $this->llaves['periodo_id'],
            'estado_solicitud_id' => $this->observada,
            'detalle' => $this->datos(), 'declaracion_veracidad' => true, 'enviada_at' => now(),
        ]);
        // gpa_verificado está fuera de $fillable: solo lo escribe SolicitudService.
        $solicitud->forceFill(['gpa_verificado' => true])->save();
        $doc = $solicitud->documentosGenerados()->create(['version' => 1, 'ruta_docx' => 'original/'.$solicitud->id.'.docx']);
        Storage::disk('local')->put($doc->ruta_docx, 'Word anterior');
        $doc->documentoFirmado()->create(['ruta' => 'original/'.$solicitud->id.'.pdf', 'tamano_bytes' => 10, 'subido_por' => $this->usuario->id]);
        Storage::disk('local')->put($doc->documentoFirmado->ruta, 'PDF anterior');
        return $solicitud;
    }

    private function datos(): array
    {
        return ['tramite' => 'gracia', 'nombre' => 'Ana Pérez', 'codigo' => '20260001',
            'carrera' => 'Computación', 'correo' => 'ana@uees.edu.ec', 'celular' => '0991234567',
            'materias' => [app(TramitesSimulados::class)->materias()[0] + ['datos' => ['nota' => '70', 'asistencia' => '80']]],
            'campos' => ['motivo' => 'Motivo corregido'], 'declaracion' => true];
    }

    private function contexto(Solicitud $solicitud): void
    {
        $request = Request::create('/', 'POST', ['solicitud_correccion' => $solicitud->id]);
        $request->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $request);
    }

    private function preparar(Solicitud $solicitud, array $datos = []): array
    {
        $this->contexto($solicitud);
        $datos = $datos ?: $this->datos();
        app(DocumentosTemporales::class)->guardar('gracia', 'Word corregido', $datos);
        app(FirmadosTemporales::class)->guardar($this->pdf(), 'gracia');
        $archivo = app(RespaldosTemporales::class)->agregar($this->pdf(), 'gracia');
        $datos['requisitos'] = [$archivo['id'] => 'respaldo'];
        $datos['clave_envio'] = app(DocumentosTemporales::class)->buscar('gracia')['version'];
        return $datos;
    }

    private function integrar(bool $fallar = false): void
    {
        $contexto = ['carrera_estudiante_id' => $this->inscripcion, 'tipo_tramite_id' => $this->llaves['tipo_tramite_id'],
            'periodo_id' => $this->llaves['periodo_id'], 'estado_solicitud_id' => $this->observada,
            'cursos' => ['calculo-demo' => $this->llaves['curso_id']], 'requisitos' => ['respaldo' => $this->llaves['documento_requerido_id']]];
        $this->app->instance(IntegracionEnvio::class, new class($fallar, $contexto, $this->enviada) implements IntegracionEnvio {
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
                if ($this->fallar) throw new \RuntimeException('Transición fallida');
                $solicitud->forceFill(['estado_solicitud_id' => $this->enviada])->save();
            }
        });
    }

    public function test_solo_el_propietario_puede_corregir_una_observada(): void
    {
        $propia = $this->solicitud();
        $this->assertTrue(Gate::allows('update', $propia));
        $this->assertFalse(Gate::allows('update', $this->solicitud($this->ajena)));
        $propia->forceFill(['estado_solicitud_id' => $this->enviada])->save();
        $this->assertFalse(Gate::allows('update', $propia->fresh()));
        $this->get(route('solicitudes.edit', $propia))->assertForbidden();
    }

    public function test_editar_recupera_datos_y_no_pisa_el_borrador_de_un_tramite_nuevo(): void
    {
        app(BorradoresTemporales::class)->guardar('gracia', ['paso' => 1, 'nombre' => 'Borrador nuevo']);
        $propia = $this->solicitud();
        $respuesta = $this->get(route('solicitudes.edit', $propia))->assertOk()->assertSee('Corregir solicitud');
        $this->assertSame($this->usuario->name, $respuesta->viewData('borrador')['nombre']);
        $this->assertSame('Borrador nuevo', app(BorradoresTemporales::class)->todos()['gracia']['nombre']);
        $this->assertArrayHasKey('correccion-'.$propia->id.'--gracia', app(BorradoresTemporales::class)->todos());
    }

    public function test_reenvia_mismo_expediente_con_version_nueva_y_conserva_documentos_anteriores(): void
    {
        $this->integrar();
        $propia = $this->solicitud(); $codigo = $propia->codigo;
        $datos = $this->preparar($propia);
        $guardada = app(EnvioSolicitud::class)->enviar($this->usuario, $datos, $propia, EnvioSolicitud::revision($propia));
        $this->assertSame($propia->id, $guardada->id);
        $this->assertSame($codigo, $guardada->codigo);
        $this->assertDatabaseCount('solicitudes', 1);
        $this->assertSame([1, 2], $guardada->documentosGenerados()->orderBy('version')->pluck('version')->all());
        $this->assertSame($this->enviada, $guardada->estado_solicitud_id);
        $this->assertFalse($guardada->gpa_verificado);
        Storage::disk('local')->assertExists('original/'.$propia->id.'.docx');
        Storage::disk('local')->assertExists('original/'.$propia->id.'.pdf');
        $this->assertCount(1, $guardada->detalle['_versiones_anteriores']);
    }

    public function test_correccion_pendiente_aparece_en_mis_solicitudes_con_su_enlace(): void
    {
        $propia = $this->solicitud();
        $this->get(route('solicitudes.edit', $propia))->assertOk();
        $this->get(route('solicitudes.index'))->assertOk()
            ->assertSee('Corrección de '.$propia->codigo)
            ->assertSee(route('solicitudes.edit', $propia), false);
    }

    public function test_mis_solicitudes_muestra_solo_las_propias_y_permite_corregir_observadas(): void
    {
        $propia = $this->solicitud(); $ajena = $this->solicitud($this->ajena);
        $this->get(route('solicitudes.index'))->assertOk()->assertSee($propia->codigo)
            ->assertDontSee($ajena->codigo)->assertSee(route('solicitudes.edit', $propia), false);
    }

    public function test_archivos_de_dos_correcciones_del_mismo_tramite_no_se_mezclan(): void
    {
        $primera = $this->solicitud(); $segunda = $this->solicitud();
        $this->preparar($primera);
        $wordPrimero = app(DocumentosTemporales::class)->buscar('gracia')['version'];
        $this->contexto($segunda);
        $this->assertNull(app(DocumentosTemporales::class)->buscar('gracia'));
        $this->assertSame([], app(RespaldosTemporales::class)->delTramite('gracia'));
        $this->contexto($primera);
        $this->assertSame($wordPrimero, app(DocumentosTemporales::class)->buscar('gracia')['version']);
    }

    public function test_no_reenvia_con_pdf_firmado_de_otra_version_de_datos(): void
    {
        $this->integrar(); $propia = $this->solicitud(); $datos = $this->preparar($propia);
        $datos['nombre'] = 'Nombre corregido después de firmar';
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(EnvioSolicitud::class)->enviar($this->usuario, $datos, $propia, EnvioSolicitud::revision($propia));
    }

    public function test_endpoint_no_finge_reenvio_si_falta_el_adaptador(): void
    {
        unset($this->app[IntegracionEnvio::class]);
        $propia = $this->solicitud();
        $facultades = \App\Services\Solicitudes\CatalogoFacultades::todas();
        $facultad = array_key_first($facultades);
        $datos = $this->datos() + ['requisitos' => [], 'cedula' => '1234567890', 'facultad' => $facultad,
            'modalidad' => 'Presencial', 'periodo' => '2026', 'clave_envio' => (string) \Illuminate\Support\Str::uuid()];
        $datos['carrera'] = $facultades[$facultad][0];
        $this->postJson(route('solicitudes.reenviar', ['solicitud' => $propia->id, 'solicitud_correccion' => $propia->id]), $datos)
            ->assertStatus(503)->assertJsonPath('message', 'El reenvío está pendiente del adaptador de estados de revisión. Tu corrección sigue guardada como borrador.');
        $this->assertSame($this->observada, $propia->fresh()->estado_solicitud_id);
    }

    public function test_no_sobrescribe_una_solicitud_que_cambio_mientras_se_editaba(): void
    {
        $this->integrar();
        $propia = $this->solicitud(); $base = EnvioSolicitud::revision($propia);
        $datos = $this->preparar($propia);
        $propia->forceFill(['detalle' => $propia->detalle + ['cambio_concurrente' => true]])->save();
        try {
            app(EnvioSolicitud::class)->enviar($this->usuario, $datos, $propia, $base);
            $this->fail('Debía rechazar una revisión desactualizada.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
        $this->assertDatabaseCount('documentos_generados', 1);
        $this->assertNotNull(app(FirmadosTemporales::class)->buscar('gracia'));
    }

    public function test_si_falla_el_estado_no_se_pierden_datos_ni_archivos(): void
    {
        $this->integrar(true);
        $propia = $this->solicitud(); $datos = $this->preparar($propia);
        try {
            app(EnvioSolicitud::class)->enviar($this->usuario, $datos, $propia, EnvioSolicitud::revision($propia));
            $this->fail('Debía fallar la transición de prueba.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Transición fallida', $error->getMessage());
        }
        $this->assertSame($this->observada, $propia->fresh()->estado_solicitud_id);
        $this->assertDatabaseCount('documentos_generados', 1);
        $this->assertSame([], Storage::disk('local')->allFiles('expedientes'));
        $this->assertNotNull(app(FirmadosTemporales::class)->buscar('gracia'));
    }

    public function test_la_correccion_y_sus_archivos_se_recuperan_en_otra_sesion(): void
    {
        $propia = $this->solicitud();
        $this->get(route('solicitudes.edit', $propia))->assertOk();
        $this->putJson(route('borradores-temporales.update', [
            'tramite' => 'gracia', 'solicitud_correccion' => $propia->id,
        ]), ['paso' => 3, 'nombre' => 'Nombre corregido', 'materias' => [], 'campos' => [], 'requisitos' => []])
            ->assertOk()->assertJsonPath('en_cuenta', true);
        $this->preparar($propia);
        app(\App\Services\Solicitudes\CorreccionesGuardadas::class)->guardar($propia, 'gracia');
        $word = app(DocumentosTemporales::class)->buscar('gracia');
        $firmado = app(FirmadosTemporales::class)->buscar('gracia');
        $respaldo = app(RespaldosTemporales::class)->delTramite('gracia')[0];
        $this->assertStringStartsWith('correcciones-g2/'.$this->usuario->id.'/'.$propia->id.'/', $word['ruta']);
        $this->flushSession();
        $borrador = $this->get(route('solicitudes.edit', $propia))->assertOk()->viewData('borrador');
        $this->assertSame($this->usuario->name, $borrador['nombre']);
        $this->assertTrue($borrador['en_cuenta']);
        $this->assertSame($word['version'], app(DocumentosTemporales::class)->buscar('gracia')['version']);
        $this->assertSame($firmado['ruta'], app(FirmadosTemporales::class)->buscar('gracia')['ruta']);
        $this->assertSame($respaldo['id'], app(RespaldosTemporales::class)->delTramite('gracia')[0]['id']);
        Storage::disk('local')->assertExists([$word['ruta'], $firmado['ruta']]);
        $this->assertSame('Ana Pérez', $propia->fresh()->detalle['nombre']);
        $this->assertDatabaseCount('documentos_generados', 1);
        Storage::disk('local')->assertExists('original/'.$propia->id.'.pdf');
    }

    public function test_descartar_la_correccion_no_borra_el_expediente_original(): void
    {
        $propia = $this->solicitud();
        $this->get(route('solicitudes.edit', $propia))->assertOk();
        $this->preparar($propia);
        app(\App\Services\Solicitudes\CorreccionesGuardadas::class)->guardar($propia, 'gracia');
        $ruta = app(DocumentosTemporales::class)->buscar('gracia')['ruta'];
        $this->flushSession();
        $this->deleteJson(route('borradores-temporales.destroy', [
            'tramite' => 'gracia', 'solicitud_correccion' => $propia->id,
        ]))->assertNoContent();
        $this->assertDatabaseCount('borradores_correccion_g2', 0);
        $this->assertDatabaseHas('solicitudes', ['id' => $propia->id, 'estado_solicitud_id' => $this->observada]);
        Storage::disk('local')->assertMissing($ruta);
        Storage::disk('local')->assertExists(['original/'.$propia->id.'.docx', 'original/'.$propia->id.'.pdf']);
    }

    public function test_otro_usuario_no_recupera_la_copia_privada_de_la_correccion(): void
    {
        $propia = $this->solicitud();
        $this->get(route('solicitudes.edit', $propia))->assertOk();
        $this->flushSession();
        $this->actingAs(User::factory()->create(['tipo' => 'estudiante', 'activo' => true]));
        $this->get(route('solicitudes.edit', $propia))->assertForbidden();
        $this->putJson(route('borradores-temporales.update', ['tramite' => 'gracia', 'solicitud_correccion' => $propia->id]),
            ['paso' => 2, 'nombre' => 'Intruso'])->assertForbidden();
        $this->assertSame([], session('borradores_temporales', []));
        $this->assertDatabaseCount('borradores_correccion_g2', 1);
    }

    public function test_advierte_si_el_expediente_cambio_despues_de_guardar_la_correccion(): void
    {
        $propia = $this->solicitud();
        $this->get(route('solicitudes.edit', $propia))->assertOk();
        $propia->forceFill(['detalle' => $propia->detalle + ['cambio_concurrente' => true]])->save();
        $this->flushSession();
        $this->get(route('solicitudes.edit', $propia))->assertOk()
            ->assertSee('La solicitud cambió desde que empezaste esta corrección.');
        $this->assertNotSame(EnvioSolicitud::revision($propia), session('correcciones_base.'.$propia->id));
    }

    public function test_reenvio_http_limpia_la_copia_de_trabajo_y_conserva_las_versiones(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $this->integrar();
        $propia = $this->solicitud();
        $this->get(route('solicitudes.edit', $propia))->assertOk();
        $facultades = \App\Services\Solicitudes\CatalogoFacultades::todas();
        $facultad = array_key_first($facultades);
        $datos = $this->datos() + ['cedula' => '1234567890', 'facultad' => $facultad,
            'modalidad' => 'Presencial', 'periodo' => '2026'];
        $datos['carrera'] = $facultades[$facultad][0];
        $datos = array_replace($datos, \App\Services\Solicitudes\DatosFormularioSolicitud::cuenta($this->usuario));
        $datos = $this->preparar($propia, $datos);
        app(\App\Services\Solicitudes\CorreccionesGuardadas::class)->guardar($propia, 'gracia');
        $ruta = app(DocumentosTemporales::class)->buscar('gracia')['ruta'];
        $this->flushSession();
        // Alterar campos de solo lectura no cambia la identidad ni invalida el documento legítimo.
        $datos['nombre'] = 'Identidad manipulada';
        $datos['correo'] = 'otra.cuenta@uees.edu.ec';
        $this->postJson(route('solicitudes.reenviar', ['solicitud' => $propia->id, 'solicitud_correccion' => $propia->id]), $datos)
            ->assertOk()->assertJsonPath('codigo', $propia->codigo);
        $this->assertSame($this->usuario->name, $propia->fresh()->detalle['nombre']);
        $this->assertSame($this->usuario->email, $propia->fresh()->detalle['correo']);
        $this->assertDatabaseCount('borradores_correccion_g2', 0);
        $this->assertSame([1, 2], $propia->documentosGenerados()->orderBy('version')->pluck('version')->all());
        Storage::disk('local')->assertMissing($ruta);
        Storage::disk('local')->assertExists('original/'.$propia->id.'.docx');
        Storage::disk('local')->assertExists($propia->documentosGenerados()->latest('version')->firstOrFail()->ruta_docx);
        \Illuminate\Support\Facades\Notification::assertSentTo($this->usuario, \App\Notifications\NotificacionPortal::class);
        \Illuminate\Support\Facades\Notification::assertCount(1);
    }
}

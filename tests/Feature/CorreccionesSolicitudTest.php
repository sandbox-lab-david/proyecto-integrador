<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\BorradoresTemporales;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\EnvioSolicitud;
use App\Services\Solicitudes\IntegracionEnvio;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorreccionesSolicitudTest extends TestCase
{
    use MateriasCatalogoPrueba, ArchivosDePrueba;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discoTemporal();
        Schema::create('users', function (Blueprint $t) { $t->id(); });
        DB::table('users')->insert([['id' => 10], ['id' => 20]]);
        // SQLite aislado: prueba el flujo de G2 sin cambiar migraciones ajenas.
        Schema::create('estudiantes', function (Blueprint $t) { $t->id(); $t->integer('user_id'); });
        Schema::create('carrera_estudiante', function (Blueprint $t) { $t->id(); $t->integer('user_id'); });
        Schema::create('estados_solicitud', function (Blueprint $t) { $t->id(); $t->string('codigo'); });
        Schema::create('solicitudes', function (Blueprint $t) {
            $t->id(); $t->string('codigo')->unique(); $t->integer('carrera_estudiante_id');
            $t->integer('tipo_tramite_id'); $t->integer('periodo_id'); $t->integer('estado_solicitud_id');
            $t->json('detalle'); $t->decimal('gpa_declarado', 5, 2)->nullable();
            $t->boolean('gpa_verificado')->default(false); $t->date('fecha_ultima_recuperacion')->nullable();
            $t->boolean('declaracion_veracidad')->default(false); $t->timestamp('enviada_at')->nullable(); $t->timestamps();
        });
        Schema::create('solicitud_materia', function (Blueprint $t) {
            $t->id(); $t->integer('solicitud_id'); $t->integer('curso_id');
            $t->decimal('nota_declarada', 5, 2)->nullable(); $t->decimal('asistencia_declarada', 5, 2)->nullable();
            $t->string('estado_declarado')->nullable(); $t->timestamps();
        });
        Schema::create('registros_anteriores', function (Blueprint $t) {
            $t->id(); $t->integer('solicitud_materia_id'); $t->timestamps();
        });
        Schema::create('respaldos', function (Blueprint $t) {
            $t->id(); $t->integer('solicitud_id'); $t->integer('documento_requerido_id')->nullable();
            $t->string('nombre_original'); $t->string('ruta'); $t->string('mime'); $t->integer('tamano_bytes'); $t->timestamps();
        });
        Schema::create('documentos_generados', function (Blueprint $t) {
            $t->id(); $t->integer('solicitud_id'); $t->integer('version'); $t->string('ruta_docx');
            $t->string('ruta_pdf')->nullable(); $t->timestamps(); $t->unique(['solicitud_id', 'version']);
        });
        Schema::create('documentos_firmados', function (Blueprint $t) {
            $t->id(); $t->integer('documento_generado_id'); $t->string('ruta'); $t->integer('tamano_bytes');
            $t->integer('subido_por'); $t->timestamps();
        });
        DB::table('estudiantes')->insert([['id' => 1, 'user_id' => 10], ['id' => 2, 'user_id' => 20]]);
        DB::table('carrera_estudiante')->insert([['id' => 1, 'user_id' => 10], ['id' => 2, 'user_id' => 20]]);
        DB::table('estados_solicitud')->insert([['id' => 1, 'codigo' => 'observado'], ['id' => 2, 'codigo' => 'enviado']]);
        $this->usuario = (new User)->forceFill(['id' => 10, 'tipo' => 'estudiante', 'activo' => true]);
        $this->actingAs($this->usuario);
    }

    private function solicitud(int $inscripcion = 1): Solicitud
    {
        $solicitud = Solicitud::create([
            'codigo' => 'SOL-2026-'.random_int(10000, 99999), 'carrera_estudiante_id' => $inscripcion,
            'tipo_tramite_id' => 1, 'periodo_id' => 1, 'estado_solicitud_id' => 1,
            'detalle' => $this->datos(), 'gpa_verificado' => true,
        ]);
        $doc = $solicitud->documentosGenerados()->create(['version' => 1, 'ruta_docx' => 'original/'.$solicitud->id.'.docx']);
        Storage::disk('local')->put($doc->ruta_docx, 'Word anterior');
        $doc->documentoFirmado()->create(['ruta' => 'original/'.$solicitud->id.'.pdf', 'tamano_bytes' => 10, 'subido_por' => 10]);
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

    private function preparar(Solicitud $solicitud): array
    {
        $this->contexto($solicitud);
        $datos = $this->datos();
        app(DocumentosTemporales::class)->guardar('gracia', 'Word corregido', $datos);
        app(FirmadosTemporales::class)->guardar($this->pdf(), 'gracia');
        $archivo = app(RespaldosTemporales::class)->agregar($this->pdf(), 'gracia');
        $datos['requisitos'] = [$archivo['id'] => 'respaldo'];
        $datos['clave_envio'] = app(DocumentosTemporales::class)->buscar('gracia')['version'];
        return $datos;
    }

    private function integrar(bool $fallar = false): void
    {
        $this->app->instance(IntegracionEnvio::class, new class($fallar) implements IntegracionEnvio {
            public function __construct(private bool $fallar) {}
            public function resolver(User $usuario, array $datos): array
            {
                return ['carrera_estudiante_id' => 1, 'tipo_tramite_id' => 1, 'periodo_id' => 1,
                    'estado_solicitud_id' => 1, 'cursos' => ['calculo-demo' => 1], 'requisitos' => ['respaldo' => 1]];
            }
            public function enviar(Solicitud $solicitud, User $usuario): void
            {
                if ($this->fallar) throw new \RuntimeException('Transición fallida');
                $solicitud->forceFill(['estado_solicitud_id' => 2])->save();
            }
        });
    }

    public function test_solo_el_propietario_puede_corregir_una_observada(): void
    {
        $propia = $this->solicitud();
        $this->assertTrue(Gate::allows('update', $propia));
        $this->assertFalse(Gate::allows('update', $this->solicitud(2)));
        $propia->forceFill(['estado_solicitud_id' => 2])->save();
        $this->assertFalse(Gate::allows('update', $propia->fresh()));
        $this->get(route('solicitudes.edit', $propia))->assertForbidden();
    }

    public function test_editar_recupera_datos_y_no_pisa_el_borrador_de_un_tramite_nuevo(): void
    {
        app(BorradoresTemporales::class)->guardar('gracia', ['paso' => 1, 'nombre' => 'Borrador nuevo']);
        $propia = $this->solicitud();
        $respuesta = $this->get(route('solicitudes.edit', $propia))->assertOk()->assertSee('Corregir solicitud');
        $this->assertSame('Ana Pérez', $respuesta->viewData('borrador')['nombre']);
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
        $this->assertSame(2, $guardada->estado_solicitud_id);
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
        $propia = $this->solicitud(); $ajena = $this->solicitud(2);
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
        $this->assertSame(1, $propia->fresh()->estado_solicitud_id);
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
        $this->assertSame(1, $propia->fresh()->estado_solicitud_id);
        $this->assertDatabaseCount('documentos_generados', 1);
        $this->assertSame([], Storage::disk('local')->allFiles('expedientes'));
        $this->assertNotNull(app(FirmadosTemporales::class)->buscar('gracia'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\DocumentoFirmado;
use App\Models\DocumentoGenerado;
use App\Models\Estudiante;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Respaldo;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\BorradoresSolicitud;
use App\Services\Solicitudes\CatalogoFacultades;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\SolicitudService;
use App\Services\Solicitudes\TramitesSimulados;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Borrador en la cuenta (RF-2.12): una fila de solicitudes en estado
 * «borrador» y sin enviada_at, que se retoma desde otra sesión y que el envío
 * convierte en la solicitud.
 */
class BorradoresSolicitudTest extends TestCase
{
    use ArchivosDePrueba, RefreshDatabase, RequisitosPrologPrueba;

    private User $usuario;

    private CarreraEstudiante $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(CatalogoInformativoSeeder::class);
        [$this->usuario, $this->inscripcion] = $this->estudianteInscrito();
        $this->actingAs($this->usuario);
    }

    /**
     * @return array{0: User, 1: CarreraEstudiante}
     */
    private function estudianteInscrito(string $codigo = '2026000001'): array
    {
        $u = User::factory()->create(['tipo' => 'estudiante']);
        $carrera = Carrera::where('codigo', 'COM')->first() ?? Carrera::create([
            'facultad_id' => DB::table('facultades')->insertGetId(['codigo' => 'ING', 'nombre' => 'Ingeniería', 'activo' => true]),
            'codigo' => 'COM', 'nombre' => 'Ingeniería en Computación', 'nivel' => 'grado', 'activo' => true,
        ]);
        Periodo::where('codigo', 'ACTUAL')->exists() || Periodo::create(['codigo' => 'ACTUAL', 'nombre' => 'Período actual', 'tipo' => 'ordinario',
            'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(30), 'semana_primer_parcial' => 4, 'activo' => true]);
        $e = Estudiante::create(['user_id' => $u->id, 'codigo' => $codigo, 'nombres' => 'Estudiante', 'apellidos' => 'Prueba', 'correo' => $u->email]);
        $cm = CarreraModalidad::firstOrCreate(['carrera_id' => $carrera->id, 'modalidad_id' => Modalidad::firstOrCreate(['nombre' => 'Presencial'])->id]);

        return [$u, CarreraEstudiante::create(['estudiante_id' => $e->id, 'carrera_modalidad_id' => $cm->id, 'estado' => 'activo'])];
    }

    private function guardar(array $datos = [], string $tramite = 'recuperacion'): TestResponse
    {
        return $this->putJson(route('borradores-temporales.update', $tramite), $datos + [
            'paso' => 2, 'celular' => '0990000000', 'materias' => [], 'campos' => [], 'requisitos' => [],
        ]);
    }

    public function test_guarda_el_borrador_en_la_cuenta_como_solicitud_sin_enviar(): void
    {
        $this->guardar(['campos' => ['gpa_general' => '71.5']])->assertOk()->assertJson(['en_cuenta' => true]);

        $borrador = Solicitud::sole();
        $this->assertNull($borrador->enviada_at);
        $this->assertSame('borrador', $borrador->estadoSolicitud->codigo);
        $this->assertSame($this->inscripcion->id, $borrador->carrera_estudiante_id);
        $this->assertSame(Periodo::where('codigo', 'ACTUAL')->value('id'), $borrador->periodo_id);
        $this->assertSame('recuperacion', $borrador->detalle['tramite']);
        $this->assertSame('71.50', $borrador->gpa_declarado);
        $this->assertFalse($borrador->declaracion_veracidad);
        $this->assertMatchesRegularExpression('/^SOL-\d{4}-\d{5}$/', $borrador->codigo);
        // Lo guardado en la cuenta no se duplica en la sesión.
        $this->assertSame([], session('borradores_temporales', []));
    }

    public function test_guardar_otra_vez_actualiza_el_mismo_borrador(): void
    {
        $this->guardar(['paso' => 1])->assertOk();
        $codigo = Solicitud::sole()->codigo;

        $this->guardar(['paso' => 3, 'campos' => ['observacion' => 'Segundo guardado']])->assertOk();

        $borrador = Solicitud::sole();
        $this->assertSame($codigo, $borrador->codigo);
        $this->assertSame(3, $borrador->detalle['paso']);
        $this->assertSame('Segundo guardado', $borrador->detalle['campos']['observacion']);
    }

    public function test_cada_tramite_tiene_su_propio_borrador(): void
    {
        $this->guardar([], 'recuperacion')->assertOk();
        $this->guardar([], 'gracia')->assertOk();

        $this->assertSame(['gracia', 'recuperacion'],
            Solicitud::borradores()->get()->pluck('detalle.tramite')->sort()->values()->all());
    }

    public function test_se_retoma_desde_otra_sesion(): void
    {
        $this->guardar(['paso' => 3, 'campos' => ['observacion' => 'Escrito en otra computadora']])->assertOk();

        $this->flushSession();

        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()->viewData('borrador');
        $this->assertSame('Escrito en otra computadora', $borrador['campos']['observacion']);
        $this->assertTrue($borrador['en_cuenta']);
    }

    public function test_mis_solicitudes_lo_ofrece_sin_terminar_y_no_como_registrada(): void
    {
        $this->guardar(['paso' => 2])->assertOk();
        $borrador = Solicitud::sole();

        $vista = $this->get(route('solicitudes.index'))->assertOk();

        $this->assertSame([['codigo' => 'recuperacion', 'paso' => 2, 'en_cuenta' => true]], array_map(
            fn ($pendiente) => array_intersect_key($pendiente, array_flip(['codigo', 'paso', 'en_cuenta'])),
            $vista->viewData('sinTerminar')
        ));
        $this->assertFalse(collect($vista->viewData('solicitudes'))->contains('id', $borrador->id));
        $this->assertSame(0, app(SolicitudService::class)->delEstudiante($this->inscripcion->estudiante_id)->count());
    }

    public function test_el_seguimiento_de_un_borrador_lleva_al_formulario(): void
    {
        $this->guardar()->assertOk();

        $this->get(route('solicitudes.show', Solicitud::sole()))
            ->assertRedirect(route('solicitudes.create', ['tramite' => 'recuperacion']));
    }

    public function test_otro_estudiante_no_ve_ni_descarta_el_borrador(): void
    {
        $this->guardar(['campos' => ['observacion' => 'Privado']])->assertOk();
        $borrador = Solicitud::sole();
        [$otro] = $this->estudianteInscrito('2026000002');

        $this->actingAs($otro);
        $this->assertNull($this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->viewData('borrador'));
        $this->get(route('solicitudes.show', $borrador))->assertForbidden();
        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->assertModelExists($borrador);
    }

    public function test_el_personal_no_ve_los_borradores(): void
    {
        $this->guardar()->assertOk();

        $this->assertFalse(User::factory()->create(['tipo' => 'trabajador'])->can('view', Solicitud::sole()));
    }

    public function test_descartar_borra_la_fila_sus_archivos_y_su_carpeta(): void
    {
        $this->guardar()->assertOk();
        $borrador = Solicitud::sole();
        $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'recuperacion', 'archivo' => $this->pdf()])->assertCreated();
        app(DocumentosTemporales::class)->guardar('recuperacion', 'Word de prueba', ['tramite' => 'recuperacion']);
        $this->postJson(route('firmados-temporales.store', 'recuperacion'), ['archivo' => $this->pdf('firmado.pdf')])->assertCreated();

        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        foreach (['solicitudes', 'respaldos', 'documentos_generados', 'documentos_firmados'] as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
        $this->assertSame([], app(RespaldosTemporales::class)->delTramite('recuperacion'));
        $this->assertSame([], Storage::disk('local')->allFiles(BorradoresSolicitud::carpeta($borrador)));
    }

    public function test_los_respaldos_quedan_en_la_cuenta_y_se_retoman_desde_otra_sesion(): void
    {
        $this->guardar()->assertOk();
        $borrador = Solicitud::sole();
        $requisito = app(TramitesSimulados::class)->buscar('recuperacion')['documentos'][0]['id'];
        $id = $this->postJson(route('respaldos-temporales.store'), [
            'tramite' => 'recuperacion', 'archivo' => $this->pdf(), 'requisito' => $requisito,
        ])->assertCreated()->json('id');

        $this->flushSession();

        $respaldo = Respaldo::sole();
        $this->assertSame($borrador->id, $respaldo->solicitud_id);
        $this->assertSame($requisito, $respaldo->documentoRequerido?->codigo);
        $this->assertStringStartsWith(BorradoresSolicitud::carpeta($borrador).'/', $respaldo->ruta);
        $subidos = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()->viewData('respaldosSubidos');
        $this->assertSame([['id' => $id, 'requisito' => $requisito]], array_map(
            fn (array $subido) => array_intersect_key($subido, array_flip(['id', 'requisito'])), $subidos
        ));
        $this->get(route('respaldos-temporales.show', $id))->assertOk();

        $this->deleteJson(route('respaldos-temporales.destroy', $id))->assertNoContent();

        $this->assertModelMissing($respaldo);
        Storage::disk('local')->assertMissing($respaldo->ruta);
    }

    public function test_otro_estudiante_no_ve_ni_quita_los_respaldos_del_borrador(): void
    {
        $this->guardar()->assertOk();
        $id = $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'recuperacion', 'archivo' => $this->pdf()])
            ->assertCreated()->json('id');
        [$otro] = $this->estudianteInscrito('2026000002');

        $this->actingAs($otro);
        $this->flushSession();
        $this->get(route('respaldos-temporales.show', $id))->assertNotFound();
        $this->deleteJson(route('respaldos-temporales.destroy', $id))->assertNoContent();

        Storage::disk('local')->assertExists(Respaldo::sole()->ruta);
    }

    public function test_el_word_y_el_pdf_firmado_quedan_en_la_cuenta(): void
    {
        $this->guardar()->assertOk();
        $borrador = Solicitud::sole();
        app(DocumentosTemporales::class)->guardar('recuperacion', 'Word de prueba', ['tramite' => 'recuperacion']);
        $version = app(DocumentosTemporales::class)->buscar('recuperacion')['version'];
        $this->postJson(route('firmados-temporales.store', 'recuperacion'), ['archivo' => $this->pdf('firmado.pdf')])->assertCreated();

        $this->flushSession();

        $vista = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk();
        $this->assertSame($version, $vista->viewData('versionDocumento'));
        $this->assertSame('firmado.pdf', $vista->viewData('firmado')['nombre_original']);
        // El formulario no recibe lo que el borrador sabe de sus archivos.
        $this->assertArrayNotHasKey('_archivos', $vista->viewData('borrador'));
        $generado = DocumentoGenerado::sole();
        $this->assertSame($borrador->id, $generado->solicitud_id);
        $this->assertSame($this->usuario->id, $generado->documentoFirmado->subido_por);
        Storage::disk('local')->assertExists([$generado->ruta_docx, $generado->documentoFirmado->ruta]);
        $this->get(route('firmados-temporales.show', 'recuperacion'))->assertOk();
        // Guardar el formulario no borra lo que se sabe del Word.
        $this->guardar(['paso' => 4])->assertOk();
        $this->assertSame($version, app(DocumentosTemporales::class)->buscar('recuperacion')['version']);
    }

    public function test_una_descarga_nueva_reemplaza_el_word_y_quita_el_firmado(): void
    {
        $this->guardar()->assertOk();
        $temporales = app(DocumentosTemporales::class);
        $temporales->guardar('recuperacion', 'Primera versión', ['tramite' => 'recuperacion']);
        $this->postJson(route('firmados-temporales.store', 'recuperacion'), ['archivo' => $this->pdf()])->assertCreated();
        $anterior = DocumentoGenerado::sole();
        $rutas = [$anterior->ruta_docx, $anterior->documentoFirmado->ruta];

        $temporales->guardar('recuperacion', 'Segunda versión', ['tramite' => 'recuperacion', 'campos' => ['observacion' => 'Otra']]);

        $this->assertModelMissing($anterior);
        $this->assertDatabaseCount('documentos_generados', 1);
        $this->assertDatabaseCount('documentos_firmados', 0);
        Storage::disk('local')->assertMissing($rutas);
        $this->assertNull(app(FirmadosTemporales::class)->buscar('recuperacion'));
    }

    public function test_el_pdf_descargado_queda_en_la_cuenta_y_una_descarga_en_word_lo_quita(): void
    {
        $this->guardar()->assertOk();
        $temporales = app(DocumentosTemporales::class);
        $temporales->guardar('recuperacion', 'Word de prueba', ['tramite' => 'recuperacion'], '%PDF-1.4 de prueba');

        $generado = DocumentoGenerado::sole();
        $this->assertStringStartsWith(BorradoresSolicitud::carpeta(Solicitud::sole()).'/', $generado->ruta_pdf);
        $this->assertSame('%PDF-1.4 de prueba', Storage::disk('local')->get($generado->ruta_pdf));
        $this->assertSame($generado->ruta_pdf, $temporales->buscar('recuperacion')['ruta_pdf']);

        $temporales->guardar('recuperacion', 'Word nuevo', ['tramite' => 'recuperacion']);

        Storage::disk('local')->assertMissing($generado->ruta_pdf);
        $this->assertNull(DocumentoGenerado::sole()->ruta_pdf);
        $this->assertNull($temporales->buscar('recuperacion')['ruta_pdf']);
    }

    public function test_no_acepta_el_pdf_firmado_antes_de_descargar_el_documento(): void
    {
        $this->guardar()->assertOk();

        $this->postJson(route('firmados-temporales.store', 'recuperacion'), ['archivo' => $this->pdf()])
            ->assertUnprocessable()->assertJsonValidationErrors(['archivo' => 'Descarga el documento']);

        $this->assertDatabaseCount('documentos_firmados', 0);
    }

    public function test_lo_que_la_sesion_subio_antes_de_existir_el_borrador_pasa_a_la_cuenta(): void
    {
        $this->inscripcion->update(['estado' => 'retirado']);
        $this->guardar(['paso' => 3])->assertOk()->assertJson(['en_cuenta' => false]);
        $id = $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'recuperacion', 'archivo' => $this->pdf()])
            ->assertCreated()->json('id');
        app(DocumentosTemporales::class)->guardar('recuperacion', 'Word de prueba', ['tramite' => 'recuperacion'], '%PDF-1.4 de prueba');
        $version = app(DocumentosTemporales::class)->buscar('recuperacion')['version'];
        $pdfSesion = session('documentos_temporales.recuperacion.ruta_pdf');
        $this->postJson(route('firmados-temporales.store', 'recuperacion'), ['archivo' => $this->pdf('firmado.pdf')])->assertCreated();
        $this->assertDatabaseCount('respaldos', 0);

        $this->inscripcion->update(['estado' => 'activo']);
        $this->guardar(['paso' => 3])->assertOk()->assertJson(['en_cuenta' => true]);
        $vista = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk();

        $borrador = Solicitud::sole();
        $this->assertSame([$id], array_column($vista->viewData('respaldosSubidos'), 'id'));
        $this->assertSame($version, $vista->viewData('versionDocumento'));
        $this->assertSame('firmado.pdf', $vista->viewData('firmado')['nombre_original']);
        $this->assertSame($borrador->id, Respaldo::sole()->solicitud_id);
        $this->assertSame(DocumentoGenerado::sole()->id, DocumentoFirmado::sole()->documento_generado_id);
        Storage::disk('local')->assertExists([Respaldo::sole()->ruta, DocumentoGenerado::sole()->ruta_docx, DocumentoGenerado::sole()->ruta_pdf, DocumentoFirmado::sole()->ruta]);
        $this->assertStringStartsWith(BorradoresSolicitud::carpeta($borrador).'/', DocumentoGenerado::sole()->ruta_pdf);
        Storage::disk('local')->assertMissing($pdfSesion);
        $this->assertSame([], session('respaldos_temporales.archivos', []));
        $this->assertNull(session('documentos_temporales.recuperacion'));
        $this->assertNull(session('firmados_temporales.recuperacion'));
    }

    public function test_sin_inscripcion_activa_el_borrador_queda_en_la_sesion(): void
    {
        $this->inscripcion->update(['estado' => 'retirado']);

        $this->guardar(['campos' => ['observacion' => 'Sin inscripción']])->assertOk()->assertJson(['en_cuenta' => false]);

        $this->assertDatabaseCount('solicitudes', 0);
        $this->assertSame('Sin inscripción', session('borradores_temporales.recuperacion.campos.observacion'));
    }

    public function test_al_poder_guardarse_en_la_cuenta_se_lleva_lo_de_la_sesion(): void
    {
        $this->inscripcion->update(['estado' => 'retirado']);
        $this->guardar(['paso' => 1])->assertOk()->assertJson(['en_cuenta' => false]);

        $this->inscripcion->update(['estado' => 'activo']);
        $this->guardar(['paso' => 2])->assertOk()->assertJson(['en_cuenta' => true]);

        $this->assertSame(2, Solicitud::sole()->detalle['paso']);
        $this->assertSame([], session('borradores_temporales', []));
        $this->assertCount(1, $this->get(route('solicitudes.index'))->viewData('sinTerminar'));
    }

    public function test_un_borrador_de_la_cuenta_sigue_ahi_aunque_falte_el_contexto(): void
    {
        $this->guardar(['paso' => 1])->assertOk();
        $this->inscripcion->update(['estado' => 'retirado']);

        $this->guardar(['paso' => 3])->assertOk()->assertJson(['en_cuenta' => true]);

        $this->assertSame(3, Solicitud::sole()->detalle['paso']);
    }

    public function test_al_enviar_el_borrador_se_convierte_en_la_solicitud(): void
    {
        // Un trámite sin materias, para no depender del catálogo académico de G1.
        $catalogo = app(TramitesSimulados::class);
        $tramite = collect(array_keys(DocumentoSolicitud::PLANTILLAS))->first(fn ($codigo) => $catalogo->buscar($codigo)['max_materias'] === 0);
        $def = $catalogo->buscar($tramite);
        $datos = CatalogoFacultades::normalizar([
            'tramite' => $tramite, 'nombre' => $this->usuario->name, 'correo' => $this->usuario->email,
            'codigo' => '2026000012', 'cedula' => '0912345678', 'celular' => '0991234567', 'facultad' => 'Ingeniería',
            'carrera' => 'Ingeniería en Computación', 'modalidad' => 'Presencial', 'periodo' => 'ACTUAL',
            'campos' => $this->camposDe($def['campos']), 'declaracion' => true, 'materias' => [],
        ]);
        $this->guardar(['paso' => 4, 'campos' => $datos['campos']], $tramite)->assertOk();
        $borrador = Solicitud::sole();

        $temporales = app(DocumentosTemporales::class);
        $temporales->guardar($tramite, app(DocumentoSolicitud::class)->generarWord($datos), $datos, '%PDF-1.4 descargado');
        $datos['clave_envio'] = $temporales->buscar($tramite)['version'];
        app(FirmadosTemporales::class)->guardar($this->pdf(), $tramite);
        // Un respaldo por documento, subido con el requisito del siguiente y reasignado después.
        $datos['requisitos'] = [];
        $ids = array_column($def['documentos'], 'id');
        foreach ($ids as $i => $requisito) {
            $subido = app(RespaldosTemporales::class)->agregar($this->pdf(), $tramite, $ids[($i + 1) % count($ids)]);
            $datos['requisitos'][$subido['id']] = $requisito;
        }

        $this->postJson(route('solicitudes.enviar'), $datos)->assertCreated()->assertJson(['codigo' => $borrador->codigo]);

        $enviada = Solicitud::sole();
        $this->assertSame($borrador->id, $enviada->id);
        $this->assertNotNull($enviada->enviada_at);
        $this->assertTrue($enviada->declaracion_veracidad);
        $this->assertSame('enviada', $enviada->estadoSolicitud->codigo);
        $this->assertSame(0, Solicitud::borradores()->count());
        $this->assertArrayNotHasKey('_archivos', $enviada->detalle);
        // Las filas del borrador se reemplazaron por las del expediente, con el requisito elegido al final.
        $respaldos = Respaldo::with('documentoRequerido')->get();
        $this->assertCount(count($ids), $respaldos);
        foreach ($respaldos as $respaldo) {
            $this->assertStringStartsWith('expedientes/', $respaldo->ruta);
            $this->assertSame($datos['requisitos'][pathinfo($respaldo->ruta, PATHINFO_FILENAME)], $respaldo->documentoRequerido?->codigo);
        }
        $generado = DocumentoGenerado::sole();
        $this->assertStringStartsWith('expedientes/', $generado->ruta_docx);
        // El PDF que se descargó pasa al expediente junto al Word.
        $this->assertStringStartsWith('expedientes/', $generado->ruta_pdf);
        $this->assertSame('%PDF-1.4 descargado', Storage::disk('local')->get($generado->ruta_pdf));
        $this->assertStringStartsWith('expedientes/', DocumentoFirmado::sole()->ruta);
        $this->assertSame([], Storage::disk('local')->allFiles(BorradoresSolicitud::carpeta($borrador)));
        $this->assertSame([], $this->get(route('solicitudes.index'))->viewData('sinTerminar'));
    }

    /**
     * @param  list<array<string, mixed>>  $campos
     * @return array<string, string>
     */
    private function camposDe(array $campos): array
    {
        $datos = [];
        foreach ($campos as $campo) {
            if (isset($campo['mostrar_si'])) {
                continue;
            }
            $datos[$campo['nombre']] = match ($campo['tipo']) {
                'numero' => str_contains($campo['nombre'], 'anio') ? '2025' : (str_contains($campo['nombre'], 'semana') ? '1' : '80'),
                'fecha' => '2025-01-01', 'hora' => '08:30', 'lista' => (string) array_key_first($campo['opciones']), default => 'Información de prueba',
            };
        }

        return $datos;
    }
}

<?php

namespace Tests\Feature;

use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Paso 3 del formulario (RF-2.9): los respaldos se suben a una carpeta
 * temporal de la sesión mientras la solicitud todavía no existe.
 */
class RespaldosTemporalesTest extends TestCase
{
    use ArchivosDePrueba;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function subir($archivo, string $tramite = 'recuperacion'): TestResponse
    {
        return $this->postJson(route('respaldos-temporales.store'), [
            'tramite' => $tramite,
            'archivo' => $archivo,
        ]);
    }

    public function test_sube_el_respaldo_y_no_revela_donde_quedo_guardado(): void
    {
        $respuesta = $this->subir($this->pdf('Certificado médico.pdf'));

        $respuesta->assertCreated()
            ->assertJsonStructure(['id', 'nombre_original', 'mime', 'tamano_bytes'])
            ->assertJsonMissingPath('ruta')
            ->assertJson(['nombre_original' => 'Certificado médico.pdf', 'mime' => 'application/pdf']);

        $archivos = Storage::disk('local')->allFiles('respaldos-temporales');
        $this->assertCount(1, $archivos);
        $this->assertStringEndsWith($respuesta->json('id').'.pdf', $archivos[0]);
    }

    public function test_rechaza_un_archivo_que_no_es_lo_que_dice_su_nombre(): void
    {
        $this->subir($this->falsoPdf())
            ->assertUnprocessable()
            ->assertJsonPath('errors.archivo.0', 'El archivo no es un PDF, JPG o PNG válido.');

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_avisa_cuando_php_descarta_el_archivo_por_su_tamano(): void
    {
        $respuesta = $this->subir($this->archivo('grande.pdf', '', UPLOAD_ERR_INI_SIZE))->assertUnprocessable();

        $this->assertStringStartsWith('El servidor no aceptó el archivo', $respuesta->json('errors.archivo.0'));
    }

    public function test_exige_archivo_y_tramite(): void
    {
        $this->postJson(route('respaldos-temporales.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['archivo', 'tramite']);

        $this->subir($this->pdf(), '../otro')->assertUnprocessable()->assertJsonValidationErrors('tramite');
    }

    public function test_la_vista_previa_trae_un_enlace_que_abre_el_archivo(): void
    {
        $id = $this->subir($this->png())->json('id');

        $vista = $this->get(route('respaldos-temporales.show', $id))->assertOk();
        $vista->assertSee('captura.png')->assertSee('Descargar');

        preg_match('/<img src="([^"]+)"/', $vista->getContent(), $coincidencia);
        $this->assertNotEmpty($coincidencia, 'La vista previa no trae la imagen.');

        $this->get(html_entity_decode($coincidencia[1]))->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_otra_sesion_no_puede_ver_ni_quitar_el_respaldo(): void
    {
        $id = $this->subir($this->pdf())->json('id');

        $this->flushSession();

        $this->get(route('respaldos-temporales.show', $id))->assertNotFound();
        $this->deleteJson(route('respaldos-temporales.destroy', $id))->assertNoContent();
        $this->assertCount(1, Storage::disk('local')->allFiles('respaldos-temporales'));
    }

    public function test_quitar_borra_el_archivo_del_servidor(): void
    {
        $id = $this->subir($this->pdf())->json('id');

        $this->deleteJson(route('respaldos-temporales.destroy', $id))->assertNoContent();

        $this->assertSame([], Storage::disk('local')->allFiles('respaldos-temporales'));
        $this->get(route('respaldos-temporales.show', $id))->assertNotFound();
    }

    public function test_el_formulario_vuelve_a_mostrar_lo_subido_solo_en_su_tramite(): void
    {
        $this->subir($this->pdf('respaldo-de-recuperacion.pdf'), 'recuperacion');

        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))
            ->assertOk()
            ->assertSee('respaldo-de-recuperacion.pdf');

        $this->get(route('solicitudes.create', ['tramite' => 'gracia']))
            ->assertOk()
            ->assertDontSee('respaldo-de-recuperacion.pdf');
    }

    public function test_limita_cuantos_archivos_guarda_una_sesion(): void
    {
        $lleno = array_fill(0, RespaldosTemporales::MAXIMO_POR_SESION, [
            'ruta' => 'respaldos-temporales/x/y.pdf',
            'mime' => 'application/pdf',
            'tamano_bytes' => 10,
            'nombre_original' => 'y.pdf',
            'tramite' => 'recuperacion',
        ]);

        $this->withSession(['respaldos_temporales' => ['carpeta' => 'respaldos-temporales/x', 'archivos' => $lleno]]);

        $respuesta = $this->subir($this->pdf())->assertUnprocessable();

        $this->assertStringStartsWith('Ya subiste 30 archivos', $respuesta->json('errors.archivo.0'));
    }

    public function test_una_sesion_nueva_borra_las_carpetas_abandonadas(): void
    {
        $disco = Storage::disk('local');
        $disco->put('respaldos-temporales/abandonada/viejo.pdf', '%PDF-1.4');
        $disco->put('respaldos-temporales/en-uso/reciente.pdf', '%PDF-1.4');
        touch($disco->path('respaldos-temporales/abandonada/viejo.pdf'), now()->subDays(2)->getTimestamp());

        $this->subir($this->pdf())->assertCreated();

        $disco->assertMissing('respaldos-temporales/abandonada/viejo.pdf');
        $disco->assertExists('respaldos-temporales/en-uso/reciente.pdf');
    }
}

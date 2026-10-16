<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * PDF firmado del paso 4 (RF-2.11): mientras la solicitud todavía no existe
 * se guarda en la carpeta temporal de la sesión, uno por trámite.
 */
class FirmadosTemporalesTest extends TestCase
{
    use ArchivosDePrueba;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function subir($archivo, string $tramite = 'recuperacion'): TestResponse
    {
        return $this->postJson(route('firmados-temporales.store', $tramite), ['archivo' => $archivo]);
    }

    private function guardados(): array
    {
        return Storage::disk('local')->allFiles('respaldos-temporales');
    }

    public function test_sube_el_pdf_firmado_y_no_revela_donde_quedo_guardado(): void
    {
        $this->subir($this->pdf('Solicitud firmada.pdf'))
            ->assertCreated()
            ->assertJsonMissingPath('ruta')
            ->assertJson(['nombre_original' => 'Solicitud firmada.pdf', 'mime' => 'application/pdf']);

        $this->assertCount(1, $this->guardados());
    }

    public function test_solo_acepta_pdf_por_su_contenido(): void
    {
        $this->subir($this->png('firmado.png'))
            ->assertUnprocessable()
            ->assertJsonPath('errors.archivo.0', 'El archivo no es un PDF válido.');

        $this->subir($this->falsoPdf())->assertUnprocessable();

        $this->assertSame([], $this->guardados());
    }

    public function test_exige_un_archivo_y_un_tramite_que_exista(): void
    {
        $this->postJson(route('firmados-temporales.store', 'recuperacion'), [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.archivo.0', 'Selecciona el PDF firmado.');

        $this->subir($this->pdf(), 'inventado')->assertNotFound();
    }

    public function test_avisa_cuando_php_descarta_el_archivo_por_su_tamano(): void
    {
        $respuesta = $this->subir($this->archivo('grande.pdf', '', UPLOAD_ERR_INI_SIZE))->assertUnprocessable();

        $this->assertStringStartsWith('El servidor no aceptó el archivo', $respuesta->json('errors.archivo.0'));
    }

    public function test_subir_otro_reemplaza_al_anterior(): void
    {
        $this->subir($this->pdf('primero.pdf'))->assertCreated();
        $this->subir($this->pdf('segundo.pdf'))->assertCreated();

        $this->assertCount(1, $this->guardados());
        $this->get(route('firmados-temporales.show', 'recuperacion'))->assertOk()->assertSee('segundo.pdf');
    }

    public function test_un_archivo_rechazado_no_borra_el_firmado_que_ya_estaba(): void
    {
        $this->subir($this->pdf('bueno.pdf'))->assertCreated();
        $this->subir($this->png())->assertUnprocessable();

        $this->assertCount(1, $this->guardados());
        $this->get(route('firmados-temporales.show', 'recuperacion'))->assertOk()->assertSee('bueno.pdf');
    }

    public function test_la_vista_previa_trae_un_enlace_que_abre_el_pdf(): void
    {
        $this->subir($this->pdf('firmado.pdf'));

        $vista = $this->get(route('firmados-temporales.show', 'recuperacion'))->assertOk();
        $vista->assertSee('firmado.pdf')->assertSee('Descargar');

        preg_match('/<iframe[^>]* src="([^"]+)"/', $vista->getContent(), $coincidencia);
        $this->assertNotEmpty($coincidencia, 'La vista previa no trae el PDF.');

        $this->get(html_entity_decode($coincidencia[1]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_cada_tramite_tiene_su_propio_firmado(): void
    {
        $this->subir($this->pdf(), 'recuperacion');

        $this->get(route('firmados-temporales.show', 'gracia'))->assertNotFound();
        $this->assertNull($this->get(route('solicitudes.create', ['tramite' => 'gracia']))->viewData('firmado'));
    }

    public function test_el_formulario_vuelve_a_mostrar_el_firmado_subido(): void
    {
        $this->assertNull($this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->viewData('firmado'));

        $this->subir($this->pdf('firmado.pdf'));

        $firmado = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()->viewData('firmado');

        $this->assertSame('firmado.pdf', $firmado['nombre_original']);
        $this->assertArrayNotHasKey('ruta', $firmado);
    }

    public function test_otra_sesion_no_puede_ver_ni_quitar_el_firmado(): void
    {
        $this->subir($this->pdf());

        $this->flushSession();

        $this->get(route('firmados-temporales.show', 'recuperacion'))->assertNotFound();
        $this->deleteJson(route('firmados-temporales.destroy', 'recuperacion'))->assertNoContent();
        $this->assertCount(1, $this->guardados());
    }

    public function test_quitar_borra_el_archivo_del_servidor(): void
    {
        $this->subir($this->pdf());

        $this->deleteJson(route('firmados-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->assertSame([], $this->guardados());
        $this->get(route('firmados-temporales.show', 'recuperacion'))->assertNotFound();
    }

    public function test_descartar_el_borrador_borra_tambien_el_firmado(): void
    {
        $this->subir($this->pdf(), 'recuperacion');
        $this->subir($this->pdf(), 'gracia');

        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->get(route('firmados-temporales.show', 'recuperacion'))->assertNotFound();
        $this->get(route('firmados-temporales.show', 'gracia'))->assertOk();
        $this->assertCount(1, $this->guardados());
    }

    public function test_comparte_la_carpeta_de_la_sesion_con_los_respaldos(): void
    {
        $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'recuperacion', 'archivo' => $this->png()]);
        $this->subir($this->pdf());

        $this->assertCount(1, Storage::disk('local')->directories('respaldos-temporales'));
        $this->assertCount(2, $this->guardados());
    }
}

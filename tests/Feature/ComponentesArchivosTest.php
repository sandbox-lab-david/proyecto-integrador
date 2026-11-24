<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * <x-subir-archivo> y <x-visor-documento> (contratos C55–C56).
 */
class ComponentesArchivosTest extends TestCase
{
    public function test_subir_archivo_usa_pdf_jpg_y_png_de_hasta_10_mb_si_no_se_indica_otra_cosa(): void
    {
        $vista = $this->blade('<x-subir-archivo name="resolucion" />');

        $vista->assertSee('name="resolucion"', false)
            ->assertSee('id="archivo-resolucion"', false)
            ->assertSee('accept=".pdf,.jpg,.jpeg,.png"', false)
            ->assertSee('data-extensiones="pdf,jpg,png"', false)
            ->assertSee('data-max-kb="10240"', false)
            ->assertSee('PDF, JPG o PNG. Máximo 10 MB.')
            ->assertSee('<ul class="subir-archivo__vista"', false)
            ->assertDontSee(' multiple', false);
    }

    public function test_subir_archivo_respeta_tipos_tamano_y_atributos_propios(): void
    {
        $vista = $this->blade(
            '<x-subir-archivo id="plantilla" name="plantilla" accept="docx" max="512" etiqueta="Plantilla del trámite" multiple :vista-previa="false" required />'
        );

        $vista->assertSee('id="plantilla"', false)
            ->assertSee('accept=".docx"', false)
            ->assertSee('data-max-kb="512"', false)
            ->assertSee('Plantilla del trámite')
            ->assertSee('DOCX. Máximo 512 KB por archivo.')
            ->assertSee(' multiple', false)
            ->assertSee('required', false)
            ->assertDontSee('<ul class="subir-archivo__vista"', false);
    }

    public function test_visor_muestra_una_imagen_con_enlaces_firmados(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('respaldos/captura.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
        ));

        $vista = $this->blade(
            '<x-visor-documento ruta="respaldos/captura.png" mime="image/png" nombre="Captura del SIAC.png" />'
        );

        $vista->assertSee('<img src="', false)
            ->assertSee('Captura del SIAC.png')
            ->assertSee('Abrir en otra pestaña')
            ->assertSee('Descargar')
            ->assertSee('signature=', false)
            ->assertDontSee('<iframe', false);
    }

    public function test_visor_muestra_un_pdf_en_un_marco(): void
    {
        $vista = $this->blade('<x-visor-documento ruta="respaldos/certificado.pdf" mime="application/pdf" />');

        $vista->assertSee('<iframe src="', false)
            ->assertSee('title="certificado.pdf"', false)
            ->assertDontSee('<img', false);
    }

    public function test_visor_ofrece_solo_la_descarga_de_lo_que_no_puede_mostrar(): void
    {
        $vista = $this->blade(
            '<x-visor-documento ruta="plantillas/retiro.docx" mime="application/vnd.openxmlformats-officedocument.wordprocessingml.document" />'
        );

        $vista->assertSee('Este tipo de archivo no se puede mostrar aquí.')
            ->assertSee('Descargar')
            ->assertDontSee('Abrir en otra pestaña')
            ->assertDontSee('<iframe', false)
            ->assertDontSee('<img', false);
    }
}

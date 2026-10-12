<?php

namespace Tests\Feature;

use App\Services\Archivos\ArchivoRechazado;
use App\Services\Archivos\ArchivoService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * ArchivoService (contratos C48–C49).
 */
class ArchivoServiceTest extends TestCase
{
    use ArchivosDePrueba;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function servicio(): ArchivoService
    {
        return app(ArchivoService::class);
    }

    public function test_guarda_el_archivo_en_el_storage_privado_con_un_nombre_propio(): void
    {
        $archivo = $this->pdf('Certificado médico.pdf');
        $tamano = $archivo->getSize();

        $guardado = $this->servicio()->guardar($archivo, 'respaldos');

        $this->assertSame(['ruta', 'mime', 'tamano_bytes', 'nombre_original'], array_keys($guardado));
        $this->assertMatchesRegularExpression('#^respaldos/[0-9a-f-]{36}\.pdf$#', $guardado['ruta']);
        $this->assertSame('application/pdf', $guardado['mime']);
        $this->assertSame($tamano, $guardado['tamano_bytes']);
        $this->assertSame('Certificado médico.pdf', $guardado['nombre_original']);
        Storage::disk('local')->assertExists($guardado['ruta']);
    }

    public function test_reconoce_jpg_y_png_por_su_contenido_aunque_el_nombre_diga_otra_cosa(): void
    {
        $jpg = $this->servicio()->guardar($this->jpg('foto.png'), 'respaldos');
        $png = $this->servicio()->guardar($this->png('captura.pdf'), 'respaldos');

        $this->assertSame('image/jpeg', $jpg['mime']);
        $this->assertStringEndsWith('.jpg', $jpg['ruta']);
        $this->assertSame('image/png', $png['mime']);
        $this->assertStringEndsWith('.png', $png['ruta']);
    }

    public function test_rechaza_un_archivo_cuyo_contenido_no_es_del_tipo_pedido(): void
    {
        try {
            $this->servicio()->guardar($this->falsoPdf(), 'respaldos');
            $this->fail('Se guardó una página web con nombre de PDF.');
        } catch (ArchivoRechazado $error) {
            $this->assertSame('El archivo no es un PDF, JPG o PNG válido.', $error->getMessage());
        }

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_solo_acepta_las_extensiones_que_se_le_piden(): void
    {
        $this->assertStringEndsWith('.jpg', $this->servicio()->guardar($this->jpg(), 'fotos', ['jpeg'])['ruta']);

        $this->expectException(ArchivoRechazado::class);
        $this->expectExceptionMessage('El archivo no es un PDF válido.');

        $this->servicio()->guardar($this->png(), 'resoluciones', ['pdf']);
    }

    public function test_rechaza_lo_que_supera_el_tamano_maximo(): void
    {
        $this->expectException(ArchivoRechazado::class);
        $this->expectExceptionMessage('El archivo supera el máximo de 1 KB.');

        $this->servicio()->guardar($this->pdf(relleno: 2048), 'respaldos', maxKb: 1);
    }

    public function test_rechaza_una_subida_que_php_no_completo(): void
    {
        $this->expectException(ArchivoRechazado::class);

        $this->servicio()->guardar($this->archivo('grande.pdf', '', UPLOAD_ERR_INI_SIZE), 'respaldos');
    }

    public function test_descargar_entrega_el_archivo_como_adjunto_con_el_nombre_indicado(): void
    {
        $guardado = $this->servicio()->guardar($this->pdf(), 'respaldos');

        $respuesta = $this->servicio()->descargar($guardado['ruta'], 'Certificado médico.pdf');

        $this->assertStringStartsWith('attachment;', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Certificado', $respuesta->headers->get('Content-Disposition'));
    }

    public function test_descargar_responde_404_si_el_archivo_no_existe_o_la_ruta_sale_de_la_carpeta(): void
    {
        foreach (['respaldos/no-existe.pdf', '../.env', ''] as $ruta) {
            try {
                $this->servicio()->descargar($ruta);
                $this->fail("Se entregó «{$ruta}».");
            } catch (NotFoundHttpException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_mostrar_abre_pdf_e_imagenes_en_el_navegador_y_descarga_lo_demas(): void
    {
        $pdf = $this->servicio()->guardar($this->pdf(), 'respaldos');
        Storage::disk('local')->put('importaciones/estudiantes.csv', "codigo,nombres\n2026000012,Ana\n");

        $enLinea = $this->servicio()->mostrar($pdf['ruta'], 'certificado.pdf');
        $descarga = $this->servicio()->mostrar('importaciones/estudiantes.csv');

        $this->assertStringStartsWith('inline;', $enLinea->headers->get('Content-Disposition'));
        $this->assertSame('application/pdf', $enLinea->headers->get('Content-Type'));
        $this->assertSame('nosniff', $enLinea->headers->get('X-Content-Type-Options'));
        $this->assertStringStartsWith('attachment;', $descarga->headers->get('Content-Disposition'));
    }

    public function test_url_temporal_abre_el_archivo_y_no_sirve_si_se_altera(): void
    {
        $guardado = $this->servicio()->guardar($this->pdf(), 'respaldos');
        Storage::disk('local')->put('respaldos/ajeno.pdf', '%PDF-1.4');

        $url = $this->servicio()->urlTemporal($guardado['ruta'], 'certificado.pdf');

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(str_replace(basename($guardado['ruta']), 'ajeno.pdf', $url))->assertForbidden();

        $this->travel(61)->minutes();
        $this->get($url)->assertForbidden();
    }

    public function test_url_temporal_puede_pedir_la_descarga(): void
    {
        $guardado = $this->servicio()->guardar($this->png(), 'respaldos');

        $this->get($this->servicio()->urlTemporal($guardado['ruta'], 'captura.png', descargar: true))
            ->assertOk()
            ->assertDownload('captura.png');
    }

    public function test_eliminar_borra_el_archivo(): void
    {
        $guardado = $this->servicio()->guardar($this->pdf(), 'respaldos');

        $this->servicio()->eliminar($guardado['ruta']);

        Storage::disk('local')->assertMissing($guardado['ruta']);
    }
}

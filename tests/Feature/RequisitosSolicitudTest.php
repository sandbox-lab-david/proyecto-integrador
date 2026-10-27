<?php

namespace Tests\Feature;

use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\RequisitosSolicitud;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequisitosSolicitudTest extends TestCase
{
    use FacultadesG1Prueba;
    use ArchivosDePrueba, SesionEstudiantePrueba;
    private array $documentosMotor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->discoTemporal();
        config(['chatbot.prolog.url' => 'http://motor.test']);
        // Esta prueba no depende de los cursos de G1.
        $this->partialMock(\App\Services\Solicitudes\MateriasSolicitud::class)
            ->shouldReceive('todas')->andReturn([]);
        $this->motor();
    }

    private function motor(?array $documentos = null): void
    {
        $documentos ??= [[
            'id' => 'certificado_prolog', 'nombre' => 'Certificado indicado por Prolog',
            'descripcion' => 'Documento recibido del motor', 'obligatorio' => true,
            'obligacion' => 'obligatorio', 'formato' => 'PDF', 'url' => null,
        ]];
        $this->documentosMotor = $documentos;
        Http::fake(['motor.test/*' => fn ($request) => Http::response([
            'version' => 3, 'kb_version' => app(\App\Services\Catalogo\CatalogoTramites::class)->publicado()['version'], 'tramite_id' => $request['tramite_id'],
            'intencion' => $request['intencion'],
            'items' => $request['intencion'] === 'consultar_requisitos' ? ['Condición enviada por Prolog'] : [],
            'documentos' => $request['intencion'] === 'consultar_documentos' ? $this->documentosMotor : [],
            'costo' => null, 'plazo' => null, 'resultado' => null, 'comprobaciones' => [],
            'aviso' => 'Advertencia del motor <script>alert(1)</script>',
        ])]);
    }

    public function test_ficha_y_formulario_muestran_requisitos_documentos_y_aviso_del_motor(): void
    {
        $this->get(route('estudiante.tramites.recuperacion'))->assertOk()
            ->assertSee('Condición enviada por Prolog')->assertSee('Certificado indicado por Prolog')
            ->assertSee('Advertencia del motor &lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()
            ->assertSee('Certificado indicado por Prolog')->assertSee('Adjuntar archivo')
            ->assertDontSee('registro-calificaciones');
        Http::assertSent(fn ($request) => $request['tramite_id'] === 'examen_recuperacion'
            && $request['intencion'] === 'consultar_documentos');
    }

    public function test_motor_caido_no_se_reemplaza_con_requisitos_locales(): void
    {
        Http::fake(fn () => throw new ConnectionException('Motor apagado'));
        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()
            ->assertSee('No se pudieron consultar los requisitos en Prolog')
            ->assertDontSee('registro-calificaciones');
        $this->postJson(route('respaldos-temporales.store'), [
            'tramite' => 'recuperacion', 'requisito' => 'certificado_prolog', 'archivo' => $this->pdf(),
        ])->assertStatus(503);
        $this->assertSame([], app(RespaldosTemporales::class)->delTramite('recuperacion'));
    }

    public function test_subida_queda_asociada_al_tipo_indicado_y_se_recupera_al_recargar(): void
    {
        $this->postJson(route('respaldos-temporales.store'), [
            'tramite' => 'recuperacion', 'requisito' => 'certificado_prolog', 'archivo' => $this->pdf(),
        ])->assertCreated()->assertJsonPath('requisito', 'certificado_prolog');
        $this->assertSame('certificado_prolog', app(RespaldosTemporales::class)->delTramite('recuperacion')[0]['requisito']);
    }

    public function test_subida_rechaza_tipo_que_no_viene_del_motor(): void
    {
        $this->postJson(route('respaldos-temporales.store'), [
            'tramite' => 'recuperacion', 'requisito' => 'inventado', 'archivo' => $this->pdf(),
        ])->assertUnprocessable()->assertJsonValidationErrors('requisito');
    }

    public function test_servidor_impide_envio_si_falta_documento_obligatorio(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Falta adjuntar');
        app(RequisitosSolicitud::class)->verificar('recuperacion', [], []);
    }

    public function test_servidor_consulta_requisitos_nuevos_antes_de_dar_por_completo(): void
    {
        $this->assertTrue(app(RequisitosSolicitud::class)->verificar('recuperacion', [['id' => 'archivo-1']],
            ['archivo-1' => 'certificado_prolog'])['disponible']);
        $this->motor([[
            'id' => 'nuevo_documento', 'nombre' => 'Documento actualizado', 'descripcion' => '',
            'obligatorio' => true, 'obligacion' => 'obligatorio', 'formato' => 'PDF', 'url' => null,
        ]]);
        $this->expectException(ValidationException::class);
        app(RequisitosSolicitud::class)->verificar('recuperacion', [['id' => 'archivo-1']], ['archivo-1' => 'certificado_prolog']);
    }

    public function test_lista_vacia_del_motor_es_distinta_de_motor_caido(): void
    {
        $this->motor([]);
        $this->assertSame([], app(RequisitosSolicitud::class)->verificar('recuperacion', [], [])['documentos']);
    }
}

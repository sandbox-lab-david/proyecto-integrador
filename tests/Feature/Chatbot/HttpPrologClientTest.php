<?php

namespace Tests\Feature\Chatbot;

use App\Services\Chatbot\Prolog\HttpPrologClient;
use App\Services\Chatbot\Prolog\PrologException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpPrologClientTest extends TestCase
{
    private const CONSULTA = ['version' => 3, 'tramite_id' => 'examen_recuperacion', 'intencion' => 'consultar_requisitos'];

    private const EVALUAR = ['version' => 3, 'tramite_id' => 'examen_recuperacion', 'intencion' => 'comprobar_referencias',
        'fecha' => '2026-10-07', 'origen_datos' => 'manual', 'perfil' => ['gpa_general' => 78]];

    private function ok(array $extra = []): array
    {
        return array_merge([
            'version' => 3, 'kb_version' => 'catalogo-informativo-001', 'tramite_id' => 'examen_recuperacion',
            'intencion' => 'consultar_requisitos', 'items' => ['Req 1'], 'documentos' => [],
            'costo' => null, 'plazo' => null, 'resultado' => null, 'comprobaciones' => [], 'aviso' => null,
        ], $extra);
    }

    private function cliente(): HttpPrologClient
    {
        return new HttpPrologClient('http://prolog.test');
    }

    private function codigo(callable $f): PrologException
    {
        try {
            $f();
        } catch (PrologException $e) {
            return $e;
        }
        $this->fail('Debio lanzar PrologException');
    }

    public function test_consulta_valida(): void
    {
        Http::fake(['prolog.test/consulta' => Http::response($this->ok())]);
        $this->assertSame(['Req 1'], $this->cliente()->consultar(self::CONSULTA)['items']);
    }

    public function test_evaluacion_valida(): void
    {
        $r = $this->ok(['intencion' => 'comprobar_referencias',
            'comprobaciones' => [['codigo' => 'gpa_general', 'texto' => 'Referencia: 75', 'datos' => ['referencia' => 75]]]]);
        Http::fake(['prolog.test/evaluar' => Http::response($r)]);
        $this->assertSame('gpa_general', $this->cliente()->evaluar(self::EVALUAR)['comprobaciones'][0]['codigo']);
    }

    public function test_motor_apagado_es_503(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 7: Failed to connect'));
        $e = $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA));
        $this->assertSame([503, 'motor_no_disponible', true], [$e->status, $e->codigo, $e->reintentable]);
    }

    public function test_timeout_es_504(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));
        $e = $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA));
        $this->assertSame([504, 'tiempo_agotado', true], [$e->status, $e->codigo, $e->reintentable]);
    }

    public function test_json_incompatible_es_502(): void
    {
        Http::fake(['*' => Http::response('esto no es json', 200)]);
        $this->assertSame(502, $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA))->status);
    }

    public function test_falta_una_clave_es_502(): void
    {
        $r = $this->ok();
        unset($r['aviso']);
        Http::fake(['*' => Http::response($r)]);
        $this->assertSame(502, $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA))->status);
    }

    public function test_consulta_con_resultado_es_502(): void
    {
        $r = $this->ok(['resultado' => ['estado' => 'cumple', 'motivos' => []]]);
        Http::fake(['*' => Http::response($r)]);
        $this->assertSame(502, $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA))->status);
    }

    public function test_sobre_de_error_del_motor_se_conserva(): void
    {
        Http::fake(['*' => Http::response(['version' => 3, 'error' => ['codigo' => 'datos_invalidos',
            'mensaje' => 'Revisa los campos.', 'reintentable' => false, 'campos' => ['perfil.promedio' => ['x']]]], 422)]);
        $e = $this->codigo(fn () => $this->cliente()->evaluar(self::EVALUAR));
        $this->assertSame([422, 'datos_invalidos'], [$e->status, $e->codigo]);
    }

    public function test_perfil_vacio_viaja_como_objeto(): void
    {
        Http::fake(['*' => Http::response($this->ok())]);
        try {
            $this->cliente()->evaluar(['perfil' => []] + self::EVALUAR);
        } catch (PrologException) {
        }
        Http::assertSent(fn ($req) => str_contains($req->body(), '"perfil":{}'));
    }

    public function test_envia_la_credencial_en_la_cabecera(): void
    {
        Http::fake(['*' => Http::response($this->ok())]);
        (new HttpPrologClient('http://prolog.test', apiKey: 'clave-secreta'))->consultar(self::CONSULTA);
        Http::assertSent(fn ($req) => $req->hasHeader('X-Api-Key', 'clave-secreta'));
    }

    public function test_credencial_rechazada_es_500_de_configuracion(): void
    {
        Http::fake(['*' => Http::response(['version' => 3, 'error' => ['codigo' => 'acceso_denegado',
            'mensaje' => 'x', 'reintentable' => false, 'campos' => []]], 403)]);
        $e = $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA));
        $this->assertSame([500, 'error_interno'], [$e->status, $e->codigo]);
    }

    public function test_503_por_limite_se_conserva_como_reintentable(): void
    {
        Http::fake(['*' => Http::response(['version' => 3, 'error' => ['codigo' => 'motor_no_disponible',
            'mensaje' => 'Demasiadas peticiones', 'reintentable' => true, 'campos' => []]], 503)]);
        $e = $this->codigo(fn () => $this->cliente()->consultar(self::CONSULTA));
        $this->assertSame([503, true], [$e->status, $e->reintentable]);
        Http::assertSentCount(1);   // un 503 del motor NO se reintenta
    }

    public function test_reintenta_si_no_hay_conexion_y_se_recupera(): void
    {
        $n = 0;
        Http::fake(function () use (&$n) {
            if (++$n === 1) {
                throw new ConnectionException('cURL error 7: Failed to connect');
            }

            return Http::response($this->ok());
        });
        $this->assertSame(['Req 1'], $this->cliente()->consultar(self::CONSULTA)['items']);
        $this->assertSame(2, $n);
    }
}

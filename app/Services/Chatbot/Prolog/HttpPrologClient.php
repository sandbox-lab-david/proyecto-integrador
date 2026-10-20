<?php

namespace App\Services\Chatbot\Prolog;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP hacia el servidor SWI-Prolog (prolog/kb/servidor.pl).
 * Traduce fallos de transporte: conexion rechazada -> 503, timeout -> 504,
 * JSON incompatible -> 502. Nunca los convierte en elegibilidad.
 */
final class HttpPrologClient implements PrologClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly float $timeout = 3.0,
        private readonly float $connectTimeout = 1.0,
        private readonly RespuestaMotorValidator $validator = new RespuestaMotorValidator(),
        private readonly string $apiKey = '',
        private readonly int $intentos = 2,
    ) {
    }

    public static function desdeConfig(): self
    {
                // HTTP-01: en produccion Laravel no habla con el motor sin credencial.
        if (app()->isProduction() && trim((string) config('chatbot.prolog.api_key', '')) === '') {
            throw new PrologException(500, 'error_interno',
                'PROLOG_API_KEY es obligatoria en produccion.');
        }
        return new self(
            (string) config('chatbot.prolog.url', 'http://127.0.0.1:8081'),
            (float) config('chatbot.prolog.timeout', 3.0),
            (float) config('chatbot.prolog.connect_timeout', 1.0),
            apiKey: (string) config('chatbot.prolog.api_key', ''),
            intentos: (int) config('chatbot.prolog.intentos', 2),
        );
    }

    public function consultar(array $entrada): array
    {
        return $this->enviar('/consulta', $entrada, false);
    }

    public function evaluar(array $entrada): array
    {
        // Un perfil vacio debe viajar como {} y no como [].
        if (isset($entrada['perfil']) && $entrada['perfil'] === []) {
            $entrada['perfil'] = new \stdClass();
        }
        return $this->enviar('/evaluar', $entrada, true);
    }

    /** GET /salud. Devuelve ['listo' => bool, 'kb_version' => ?string]. */
    public function salud(): array
    {
        $resp = $this->llamar(fn ($http) => $http->get('/salud'));
        $json = $resp->json();
        if (!is_array($json) || ($json['version'] ?? null) !== 2 || !isset($json['estado'])) {
            throw PrologException::respuestaInvalida('/salud con formato inesperado');
        }
        return ['listo' => $json['estado'] === 'listo' && $resp->status() === 200,
                'kb_version' => $json['kb_version'] ?? null];
    }

    private function enviar(string $ruta, array $entrada, bool $esEvaluacion): array
    {
        $cuerpo = json_encode($entrada, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $resp = $this->llamar(fn ($http) => $http->withBody($cuerpo, 'application/json')->post($ruta));

        if ($resp->status() === 200) {
            $json = $resp->json();
            if (!is_array($json)) {
                throw PrologException::respuestaInvalida('el cuerpo no es un objeto JSON');
            }
            return $this->validator->validar($json, $entrada, $esEvaluacion);
        }

        $this->lanzarErrorDelMotor($resp->status(), $resp->json());
    }

    /**
     * Envia la peticion. Reintenta SOLO si no se pudo conectar (p. ej. el motor
     * se esta reiniciando): consultar/evaluar no modifican nada, asi que repetir
     * es seguro. Un timeout no se reintenta para respetar el presupuesto de tiempo,
     * y un 503 del motor (sobrecarga/limite) tampoco, para no empeorarlo.
     */
    private function llamar(callable $peticion): \Illuminate\Http\Client\Response
    {
        $maximo = max(1, $this->intentos);

        for ($intento = 1; ; $intento++) {
            try {
                $http = Http::baseUrl(rtrim($this->baseUrl, '/'))
                    ->connectTimeout($this->connectTimeout)
                    ->timeout($this->timeout)
                    ->acceptJson();
                if ($this->apiKey !== '') {
                    $http = $http->withHeaders(['X-Api-Key' => $this->apiKey]);
                }
                return $peticion($http);
            } catch (ConnectionException $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'cURL error 28') || stripos($msg, 'timed out') !== false) {
                    throw PrologException::tiempoAgotado($e);
                }
                if ($intento >= $maximo) {
                    throw PrologException::noDisponible($e);   // rechazada, DNS, reset...
                }
                usleep(150_000);
            }
        }
    }

    private function lanzarErrorDelMotor(int $status, mixed $json): never
    {
        if (in_array($status, [401, 403], true)) {
            // El motor rechazo la credencial: es un fallo de configuracion, no del usuario.
            throw new PrologException(500, 'error_interno',
                'El motor rechazo la credencial del puente (revisar PROLOG_API_KEY).');
        }

        $err = is_array($json) ? ($json['error'] ?? null) : null;
        $valido = is_array($err)
            && ($json['version'] ?? null) === 2
            && is_string($err['codigo'] ?? null)
            && is_string($err['mensaje'] ?? null)
            && is_bool($err['reintentable'] ?? null)
            && is_array($err['campos'] ?? null);

        if ($valido && in_array($status, [422, 500, 503], true)) {
            throw new PrologException($status, $err['codigo'], $err['mensaje'], $err['reintentable'], $err['campos']);
        }
        throw PrologException::respuestaInvalida("HTTP $status sin sobre de error valido");
    }
}

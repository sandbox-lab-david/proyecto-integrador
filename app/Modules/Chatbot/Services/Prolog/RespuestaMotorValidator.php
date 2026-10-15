<?php

namespace App\Modules\Chatbot\Services\Prolog;

/**
 * Valida que el JSON recibido de Prolog cumpla el contrato v2.
 * No depende de Laravel. Si algo no encaja lanza PrologException (502).
 */
final class RespuestaMotorValidator
{
    private const CLAVES = ['version', 'kb_version', 'tramite_id', 'intencion', 'items',
        'documentos', 'costo', 'plazo', 'resultado', 'aviso'];
    private const ESTADOS = ['cumple', 'no_cumple', 'requiere_revision'];

    /** @return array respuesta limpia (solo claves del contrato) */
    public function validar(array $r, array $entrada, bool $esEvaluacion): array
    {
        foreach (self::CLAVES as $k) {
            if (!array_key_exists($k, $r)) {
                $this->fallo("falta la clave '$k'");
            }
        }
        $r = array_intersect_key($r, array_flip(self::CLAVES));

        if ($r['version'] !== 2) $this->fallo("version debe ser 2");
        if (!is_string($r['kb_version']) || $r['kb_version'] === '') $this->fallo('kb_version debe ser string');
        if ($r['tramite_id'] !== ($entrada['tramite_id'] ?? null)) $this->fallo('tramite_id no coincide con la peticion');
        if ($r['intencion'] !== ($entrada['intencion'] ?? null)) $this->fallo('intencion no coincide con la peticion');

        $this->listaDeStrings($r['items'], 'items');

        $this->lista($r['documentos'], 'documentos');
        foreach ($r['documentos'] as $i => $d) {
            $this->objeto($d, ['id', 'nombre', 'descripcion', 'obligatorio', 'formato', 'url'], "documentos[$i]");
            foreach (['id', 'nombre', 'descripcion', 'formato'] as $k) {
                if (!is_string($d[$k])) $this->fallo("documentos[$i].$k debe ser string");
            }
            if (!is_bool($d['obligatorio'])) $this->fallo("documentos[$i].obligatorio debe ser booleano");
            if ($d['url'] !== null && !is_string($d['url'])) $this->fallo("documentos[$i].url debe ser string o null");
        }

        if ($r['costo'] !== null) {
            $this->objeto($r['costo'], ['centavos', 'moneda'], 'costo');
            if (!is_int($r['costo']['centavos'])) $this->fallo('costo.centavos debe ser entero');
            if (!is_string($r['costo']['moneda'])) $this->fallo('costo.moneda debe ser string');
        }

        if ($r['plazo'] !== null) {
            $this->objeto($r['plazo'], ['texto', 'inicio', 'fin'], 'plazo');
            if (!is_string($r['plazo']['texto'])) $this->fallo('plazo.texto debe ser string');
            foreach (['inicio', 'fin'] as $k) {
                $f = $r['plazo'][$k];
                if ($f !== null && !$this->fechaIso($f)) $this->fallo("plazo.$k debe ser YYYY-MM-DD o null");
            }
        }

        if (!$esEvaluacion && $r['resultado'] !== null) $this->fallo('/consulta debe devolver resultado=null');
        if ($esEvaluacion && $r['resultado'] === null) $this->fallo('/evaluar no puede devolver resultado=null');
        if ($r['resultado'] !== null) {
            $this->objeto($r['resultado'], ['estado', 'motivos'], 'resultado');
            if (!in_array($r['resultado']['estado'], self::ESTADOS, true)) $this->fallo('resultado.estado no admitido');
            $this->lista($r['resultado']['motivos'], 'resultado.motivos');
            foreach ($r['resultado']['motivos'] as $i => $m) {
                $this->objeto($m, ['codigo', 'texto'], "motivos[$i]");
                if (!is_string($m['codigo']) || !is_string($m['texto'])) $this->fallo("motivos[$i] debe tener strings");
            }
        }

        if ($r['aviso'] !== null && !is_string($r['aviso'])) $this->fallo('aviso debe ser string o null');

        return $r;
    }

    private function fallo(string $detalle): never
    {
        throw PrologException::respuestaInvalida($detalle);
    }

    private function lista(mixed $v, string $ctx): void
    {
        if (!is_array($v) || !array_is_list($v)) $this->fallo("$ctx debe ser una lista");
    }

    private function listaDeStrings(mixed $v, string $ctx): void
    {
        $this->lista($v, $ctx);
        foreach ($v as $x) {
            if (!is_string($x)) $this->fallo("$ctx debe contener solo strings");
        }
    }

    private function objeto(mixed $v, array $claves, string $ctx): void
    {
        if (!is_array($v) || array_is_list($v) && $v !== []) $this->fallo("$ctx debe ser un objeto");
        foreach ($claves as $k) {
            if (!array_key_exists($k, $v)) $this->fallo("$ctx: falta '$k'");
        }
    }

    private function fechaIso(mixed $f): bool
    {
        if (!is_string($f) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $f, $m)) return false;
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
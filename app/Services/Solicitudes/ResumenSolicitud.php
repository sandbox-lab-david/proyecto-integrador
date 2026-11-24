<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Lo que el estudiante declaró en una solicitud, listo para mostrar en
 * <x-resumen-solicitud> (contrato C57): etiquetas del catálogo de G4, opciones
 * de las listas con su texto y fechas en d/m/Y. Lee solo lo guardado en
 * solicitudes.detalle; no consulta a Prolog ni verifica nada.
 */
class ResumenSolicitud
{
    public function __construct(
        private readonly TramitesSimulados $tramites,
    ) {}

    /**
     * @return array{
     *     personales: list<array{etiqueta: string, valor: string}>,
     *     campos: list<array{etiqueta: string, valor: string, largo: bool}>,
     *     materias: list<array{titulo: string, contexto: string, datos: list<array{etiqueta: string, valor: string, largo: bool}>}>,
     *     nombre_tramite: ?string
     * }
     */
    public function de(Solicitud $solicitud): array
    {
        $detalle = is_array($solicitud->detalle) ? $solicitud->detalle : [];
        $codigo = $detalle['tramite'] ?? null;
        $definicion = is_string($codigo) ? $this->tramites->buscar($codigo) : null;

        $personales = [];
        foreach (DatosFormularioSolicitud::PERSONALES as $clave => [$etiqueta]) {
            $valor = $this->texto($detalle[$clave] ?? null);
            if ($valor !== null) {
                $personales[] = ['etiqueta' => $etiqueta, 'valor' => $valor];
            }
        }

        $campos = is_array($detalle['campos'] ?? null) ? $detalle['campos'] : [];

        $materias = [];
        foreach (is_array($detalle['materias'] ?? null) ? $detalle['materias'] : [] as $materia) {
            if (! is_array($materia)) {
                continue;
            }
            $titulo = implode(' · ', array_filter([$this->texto($materia['codigo'] ?? null), $this->texto($materia['nombre'] ?? null)]));
            $contexto = array_filter([
                $this->texto($materia['periodo'] ?? null),
                ($paralelo = $this->texto($materia['paralelo'] ?? null)) !== null ? 'Paralelo '.$paralelo : null,
                $this->texto($materia['docente'] ?? null),
            ]);
            $materias[] = [
                'titulo' => $titulo !== '' ? $titulo : 'Materia sin nombre',
                'contexto' => implode(' · ', $contexto),
                'datos' => $this->valores($definicion['campos_materia'] ?? [], is_array($materia['datos'] ?? null) ? $materia['datos'] : []),
            ];
        }

        return [
            'personales' => $personales,
            'campos' => $this->valores($definicion['campos'] ?? [], $campos),
            'materias' => $materias,
            'nombre_tramite' => $definicion['nombre'] ?? null,
        ];
    }

    /**
     * Los campos en el orden del catálogo; lo que el catálogo ya no define
     * (cambió después del envío) se muestra al final con su nombre interno.
     *
     * @return list<array{etiqueta: string, valor: string, largo: bool}>
     */
    private function valores(array $definiciones, array $datos): array
    {
        $filas = [];
        $declarados = $datos;
        foreach ($definiciones as $campo) {
            $nombre = $campo['nombre'] ?? null;
            if (! is_string($nombre) || ! array_key_exists($nombre, $datos)) {
                continue;
            }
            unset($datos[$nombre]);
            $condicion = $campo['mostrar_si'] ?? null;
            if (is_array($condicion) && ! in_array($declarados[$condicion['campo'] ?? ''] ?? null, (array) ($condicion['valor'] ?? []), true)) {
                continue;
            }
            $valor = $this->formatear($campo, $declarados[$nombre]);
            if ($valor === null) {
                continue;
            }
            $filas[] = ['etiqueta' => $campo['etiqueta'] ?? $nombre, 'valor' => $valor, 'largo' => ($campo['tipo'] ?? null) === 'texto_largo'];
        }
        foreach ($datos as $nombre => $valor) {
            $valor = $this->texto($valor);
            if ($valor !== null) {
                $filas[] = ['etiqueta' => Str::ucfirst(str_replace('_', ' ', (string) $nombre)), 'valor' => $valor, 'largo' => false];
            }
        }

        return $filas;
    }

    private function formatear(array $campo, mixed $valor): ?string
    {
        $texto = $this->texto($valor);
        if ($texto === null) {
            return null;
        }

        return match ($campo['tipo'] ?? null) {
            'lista' => (string) ($campo['opciones'][$texto] ?? $texto),
            'fecha' => rescue(fn () => Carbon::createFromFormat('!Y-m-d', $texto)->format('d/m/Y'), $texto, false),
            default => $texto,
        };
    }

    private function texto(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }
        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }
}

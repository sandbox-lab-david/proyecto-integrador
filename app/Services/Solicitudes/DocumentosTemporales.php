<?php

namespace App\Services\Solicitudes;

use App\Services\Archivos\ArchivoService;
use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/** Word que efectivamente se descargó y datos de esa versión, privados de la sesión. */
class DocumentosTemporales
{
    public function __construct(private readonly Session $sesion, private readonly RespaldosTemporales $respaldos) {}

    public function guardar(string $tramite, string $word, array $datos): void
    {
        $ruta = $this->respaldos->carpeta().'/'.Str::uuid().'.docx';
        if (! Storage::disk(ArchivoService::DISCO)->put($ruta, $word)) {
            throw new RuntimeException('No se pudo conservar el documento generado.');
        }
        $this->quitar($tramite);
        $this->sesion->put('documentos_temporales.'.$tramite, [
            'ruta' => $ruta, 'version' => (string) Str::uuid(), 'datos' => $datos, 'hash_datos' => $this->huella($datos),
        ]);
    }

    public function buscar(string $tramite): ?array
    {
        return $this->sesion->get('documentos_temporales.'.$tramite);
    }

    public function huella(array $datos): string
    {
        $contenido = array_intersect_key($datos, array_flip(['tramite', ...array_keys(DatosFormularioSolicitud::PERSONALES), 'materias', 'campos']));
        $ordenar = function (array $valor) use (&$ordenar): array {
            if (! array_is_list($valor)) ksort($valor);
            foreach ($valor as &$item) {
                if (is_array($item)) $item = $ordenar($item);
            }
            return $valor;
        };
        return hash('sha256', json_encode($ordenar($contenido), JSON_THROW_ON_ERROR));
    }

    public function quitar(string $tramite): void
    {
        if ($documento = $this->buscar($tramite)) {
            Storage::disk(ArchivoService::DISCO)->delete($documento['ruta']);
        }
        $this->sesion->forget('documentos_temporales.'.$tramite);
    }
}

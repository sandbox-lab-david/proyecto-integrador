<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\Archivos\ArchivoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoController extends Controller
{
    public function __construct(private readonly ArchivoService $archivos) {}

    /**
     * Entrega un archivo privado a quien trae un enlace firmado por
     * ArchivoService::urlTemporal(). La ruta exige el middleware «signed».
     */
    public function ver(Request $request): StreamedResponse
    {
        $ruta = (string) $request->query('ruta');
        $nombre = $request->query('nombre');

        return $request->boolean('descargar')
            ? $this->archivos->descargar($ruta, $nombre)
            : $this->archivos->mostrar($ruta, $nombre);
    }
}

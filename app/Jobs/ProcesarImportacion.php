<?php

namespace App\Jobs;

use App\Models\Importacion;
use App\Services\Academico\ImportacionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcesarImportacion implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(public Importacion $importacion) {}

    public function handle(ImportacionService $servicio): void
    {
        $servicio->procesar($this->importacion);
    }

    public function failed(?Throwable $error): void
    {
        $this->importacion->update([
            'estado' => 'fallida',
            'errores' => [['fila' => null, 'codigo' => null, 'motivo' => 'El proceso se detuvo: '.($error?->getMessage() ?? 'error desconocido')]],
        ]);
    }
}

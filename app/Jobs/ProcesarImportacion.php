<?php

namespace App\Jobs;

use App\Models\Importacion;
use App\Services\Academico\ImportacionService;
use App\Services\Notificaciones\NotificacionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcesarImportacion implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(public Importacion $importacion, public bool $avisar = false) {}

    public function handle(ImportacionService $servicio): void
    {
        $servicio->procesar($this->importacion);
        $this->avisar();
    }

    public function failed(?Throwable $error): void
    {
        $this->importacion->update([
            'estado' => 'fallida',
            'errores' => [['fila' => null, 'codigo' => null, 'motivo' => 'El proceso se detuvo: '.($error?->getMessage() ?? 'error desconocido')]],
        ]);
        $this->avisar();
    }

    // C53
    private function avisar(): void
    {
        $importacion = $this->importacion->fresh('user');

        if (! $this->avisar || ! $importacion?->user) {
            return;
        }

        $mensaje = $importacion->estado === 'completada'
            ? "{$importacion->creadas} creadas, {$importacion->actualizadas} actualizadas y {$importacion->rechazadas} rechazadas."
            : 'No se pudo completar. Revisa el detalle.';

        try {
            app(NotificacionService::class)->notificarUsuarios(
                [$importacion->user],
                "Importación #{$importacion->id} ".($importacion->estado === 'completada' ? 'terminada' : 'fallida'),
                "{$importacion->nombreArchivo()}: {$mensaje}",
                route('cargas.detalle', $importacion, false),
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Catalogo\ExportadorProlog;
use Illuminate\Console\Command;

class ExportarConocimiento extends Command
{
    protected $signature = 'kb:exportar {--publicar : Validar, recargar Prolog y activar esta revisión}';

    protected $description = 'Exporta el catálogo en BD a una revisión validada de Prolog';

    public function handle(ExportadorProlog $exportador): int
    {
        try {
            $r = $exportador->exportar((bool) $this->option('publicar'));
            $this->info(($r->activa ? 'Publicada: ' : 'Candidata validada: ').$r->version);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

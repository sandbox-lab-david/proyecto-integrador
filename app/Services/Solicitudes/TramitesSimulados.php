<?php

namespace App\Services\Solicitudes;

use App\Services\Catalogo\CatalogoTramites;

/** Adaptador del catálogo publicado para los formularios existentes. */
class TramitesSimulados
{
    public function buscar(string $codigo): ?array
    {
        return app(CatalogoTramites::class)->buscar($codigo)['formulario'] ?? null;
    }

    /** Datos ficticios exclusivos de fixtures; nunca se ofrecen al estudiante. */
    public function materias(): array
    {
        return [
            [
                'id' => 'calculo-demo',
                'codigo' => 'MAT202',
                'nombre' => 'Cálculo II',
                'periodo' => 'Ordinario I 2026',
                'paralelo' => 'A',
                'docente' => 'Docente de prueba A',
            ],
            [
                'id' => 'fisica-demo',
                'codigo' => 'FIS101',
                'nombre' => 'Física I',
                'periodo' => 'Ordinario I 2026',
                'paralelo' => 'B',
                'docente' => 'Docente de prueba B',
            ],
            [
                'id' => 'programacion-demo',
                'codigo' => 'COM203',
                'nombre' => 'Programación II',
                'periodo' => 'Ordinario II 2025',
                'paralelo' => 'A',
                'docente' => 'Docente de prueba C',
            ],
        ];
    }
}

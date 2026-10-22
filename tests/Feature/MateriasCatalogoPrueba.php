<?php

namespace Tests\Feature;

use App\Services\Solicitudes\MateriasSolicitud;

/** Datos aislados para las pruebas anteriores de documentos y archivos. */
trait MateriasCatalogoPrueba
{
    use RequisitosPrologPrueba;

    protected function setUpMateriasCatalogoPrueba(): void
    {
        $this->withoutVite();
        $this->partialMock(MateriasSolicitud::class)->shouldReceive('todas')->andReturn([
            ['id' => 'calculo-demo', 'codigo' => 'MAT202', 'nombre' => 'Cálculo II', 'periodo' => 'Ordinario I 2026', 'paralelo' => 'A', 'docente' => 'Docente de prueba A'],
            ['id' => 'fisica-demo', 'codigo' => 'FIS101', 'nombre' => 'Física I', 'periodo' => 'Ordinario I 2026', 'paralelo' => 'B', 'docente' => 'Docente de prueba B'],
            ['id' => 'programacion-demo', 'codigo' => 'COM203', 'nombre' => 'Programación II', 'periodo' => 'Ordinario II 2025', 'paralelo' => 'A', 'docente' => 'Docente de prueba C'],
        ]);
    }
}

<?php

namespace Tests\Feature;

use Database\Seeders\CarreraSeeder;
use Database\Seeders\FacultadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Facultades y carreras de los selectores del paso 1. Salen de los seeders
 * de G1, su dueño (regla 9 de modelos.xlsx), porque CatalogoFacultades las
 * lee de su catálogo académico.
 */
trait FacultadesG1Prueba
{
    use RefreshDatabase;

    protected function setUpFacultadesG1Prueba(): void
    {
        $this->seed([FacultadSeeder::class, CarreraSeeder::class]);
    }
}

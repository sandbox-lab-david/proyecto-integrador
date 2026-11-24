<?php

namespace Tests\Feature;

use App\Models\User;

/** Identidad de sesión aislada; no crea datos académicos en el entorno local. */
trait SesionEstudiantePrueba
{
    protected function setUpSesionEstudiantePrueba(): void
    {
        $this->actingAs((new User)->forceFill([
            'id' => 900001, 'name' => 'Estudiante de prueba', 'email' => 'prueba@uees.edu.ec',
            'tipo' => 'estudiante', 'activo' => true,
        ]));
    }
}

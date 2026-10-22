<?php

namespace Database\Factories;

use App\Models\Facultad;
use App\Models\FacultadTrabajador;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacultadTrabajador>
 */
class FacultadTrabajadorFactory extends Factory
{
    protected $model = FacultadTrabajador::class;

    public function definition(): array
    {
        return [
            'trabajador_id' => Trabajador::factory(),
            'facultad_id' => Facultad::factory(),
        ];
    }
}

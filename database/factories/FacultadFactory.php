<?php

namespace Database\Factories;

use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facultad>
 */
class FacultadFactory extends Factory
{
    protected $model = Facultad::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper($this->faker->unique()->lexify('F???')),
            'nombre' => 'Facultad de '.$this->faker->unique()->words(2, true),
            'correo_notificaciones' => null,
            'activo' => true,
        ];
    }
}

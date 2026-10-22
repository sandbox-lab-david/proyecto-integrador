<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rol>
 */
class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        $codigo = 'rol_'.fake()->unique()->numberBetween(1, 99999);

        return [
            'codigo' => $codigo,
            'nombre' => "Rol {$codigo}",
            'descripcion' => fake()->sentence(),
        ];
    }
}

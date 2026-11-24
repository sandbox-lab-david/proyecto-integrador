<?php

namespace Database\Factories;

use App\Models\CarreraModalidad;
use App\Models\Carrera;
use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarreraModalidad>
 */
class CarreraModalidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'carrera_id'   => Carrera::factory(),
        'modalidad_id' => fn () => Modalidad::query()->inRandomOrder()->value('id') ?? Modalidad::factory(),
        ];
    }
}

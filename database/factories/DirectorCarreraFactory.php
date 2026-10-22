<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\DirectorCarrera;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DirectorCarrera>
 */
class DirectorCarreraFactory extends Factory
{
    protected $model = DirectorCarrera::class;

    public function definition(): array
    {
        return [
            'carrera_id' => Carrera::factory(),
            'trabajador_id' => Trabajador::factory()->conRol('director'),
            'fecha_inicio' => now()->subYear()->toDateString(),
            'fecha_fin' => null,
        ];
    }

    public function terminado(): static
    {
        return $this->state(fn () => [
            'fecha_inicio' => now()->subYears(2)->toDateString(),
            'fecha_fin' => now()->subYear()->toDateString(),
        ]);
    }
}

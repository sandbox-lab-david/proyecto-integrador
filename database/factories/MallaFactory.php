<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\Malla;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Malla>
 */
class MallaFactory extends Factory
{
    protected $model = Malla::class;

    public function definition(): array
    {
        return [
            'carrera_id' => Carrera::factory(),
            'version' => (string) fake()->unique()->numberBetween(2000, 9999),
            'vigente' => false,
        ];
    }

    public function vigente(): static
    {
        return $this->state(fn () => ['vigente' => true]);
    }
}

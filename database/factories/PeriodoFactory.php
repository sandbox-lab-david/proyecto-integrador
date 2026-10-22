<?php

namespace Database\Factories;

use App\Models\Periodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Periodo>
 */
class PeriodoFactory extends Factory
{
    protected $model = Periodo::class;

    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-1 year', '+1 year');
        $numero = fake()->unique()->numberBetween(1, 9999);

        return [
            'codigo' => "PER-{$numero}",
            'nombre' => "Periodo de prueba {$numero}",
            'tipo' => 'ordinario',
            'fecha_inicio' => $inicio->format('Y-m-d'),
            'fecha_fin' => (clone $inicio)->modify('+16 weeks')->format('Y-m-d'),
            'semana_primer_parcial' => 8,
            'activo' => false,
        ];
    }

    public function activo(): static
    {
        return $this->state(fn () => [
            'fecha_inicio' => now()->subWeeks(4)->toDateString(),
            'fecha_fin' => now()->addWeeks(12)->toDateString(),
            'activo' => true,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Importacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Importacion>
 */
class ImportacionFactory extends Factory
{
    protected $model = Importacion::class;

    public function definition(): array
    {
        $entidad = fake()->randomElement(['carreras', 'materias', 'mallas', 'docentes', 'paralelos', 'estudiantes']);
        $creadas = fake()->numberBetween(0, 50);
        $actualizadas = fake()->numberBetween(0, 20);

        return [
            'entidad' => $entidad,
            'archivo_ruta' => 'importaciones/'.Str::uuid()."/{$entidad}.csv",
            'user_id' => User::factory(),
            'estado' => 'completada',
            'total_filas' => $creadas + $actualizadas,
            'creadas' => $creadas,
            'actualizadas' => $actualizadas,
            'rechazadas' => 0,
            'errores' => null,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => ['estado' => 'pendiente', 'creadas' => 0, 'actualizadas' => 0]);
    }
}

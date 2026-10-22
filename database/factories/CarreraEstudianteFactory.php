<?php

namespace Database\Factories;

use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarreraEstudiante>
 */
class CarreraEstudianteFactory extends Factory
{
    protected $model = CarreraEstudiante::class;

    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'carrera_modalidad_id' => CarreraModalidad::factory(),
            'malla_id' => null,
            'fecha_inscripcion' => $this->faker->dateTimeBetween('-4 years', '-1 month')->format('Y-m-d'),
            'fecha_fin' => null,
            'estado' => 'activo',
        ];
    }
}

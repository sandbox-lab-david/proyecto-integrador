<?php

namespace Database\Factories;

use App\Models\CarreraEstudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarreraEstudianteFactory extends Factory
{
    protected $model = CarreraEstudiante::ofModel ?? CarreraEstudiante::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'estudiante_id' => 1,
            'carrera_id' => 1,
            'periodo_id' => 1,
            'carrera_modalidad_id' => 1,
            'malla_id' => 1,
            'fechas' => $this->faker->date(),
            'estado' => 'activo',
        ];
    }
}
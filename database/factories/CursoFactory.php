<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Trabajador;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    
    protected $model = Curso::class;

    /**
     * @return array<string, mixed>
     */ 
    public function definition(): array
    {
        return [
            // factory() le dice: "Si no me das un ID de materia, crea una materia nueva y asígnala"
            'materia_id' => Materia::factory(), 
            'periodo_id' => Periodo::factory(),
            'profesor_id' => Trabajador::factory(),
            'paralelo' => $this->faker->bothify('?##'), 
            'cupo' => $this->faker->numberBetween(20, 50),
        ];
    }
}

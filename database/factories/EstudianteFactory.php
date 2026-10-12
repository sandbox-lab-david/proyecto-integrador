<?php

namespace Database\Factories;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'codigo' => fake()->unique()->numerify('##########'),
            'correo' => fake()->unique()->userName() . '@uees.edu.ec',
            'nombres' => fake()->firstName(), 
            'apellidos' => fake()->lastName(),
            'cedula' => fake()->unique()->numerify('##########'),
            'celular' => fake()->numerify('09########'),
        ];
    }
}

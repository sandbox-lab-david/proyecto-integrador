<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            FacultadSeeder::class,
            CarreraSeeder::class,
            PeriodoSeeder::class,
            UsuarioDemoSeeder::class,
            CursoSeeder::Class,
            // Aquí agregarán luego los otros seeders
        ]);


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

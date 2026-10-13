<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Grupo 1
        $this->call([
            UsuarioDemoSeeder::class,
            //CursoSeeder::Class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Grupo 3
        $this->call([
            SolicitudRevisionSeeder::class,
        ]);
    }
}
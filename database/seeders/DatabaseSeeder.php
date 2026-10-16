<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

<<<<<<< HEAD
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
=======
    public function run(): void
    {
        // Grupo 1
        $this->call([
            UsuarioDemoSeeder::class,
            //CursoSeeder::Class,
        ]);
>>>>>>> 14da9b03ae2b6f3bbe13fb26951d91d301d13bd9

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
<<<<<<< HEAD
    }
}
=======

        // Grupo 3
        $this->call([
            SolicitudRevisionSeeder::class,
        ]);
    }
}
>>>>>>> 14da9b03ae2b6f3bbe13fb26951d91d301d13bd9

<?php

namespace Database\Seeders;

use App\Models\CategoriaMateria;
use Illuminate\Database\Seeder;

class CategoriaMateriaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Básica', 'Profesional', 'General'] as $nombre) {
            CategoriaMateria::create(['nombre' => $nombre]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Facultad;
use Illuminate\Database\Seeder;

class FacultadSeeder extends Seeder
{
    public const FACULTADES = [
        'ING' => 'Ingeniería',
        'ARQ' => 'Arquitectura y Diseño',
        'COM' => 'Ciencias de la Comunicación',
        'SAL' => 'Ciencias de la Salud “Dr. Enrique Ortega Moreira”',
        'DER' => 'Derecho, Política y Desarrollo',
        'ENE' => 'Emprendimiento, Negocios y Economía',
        'EIN' => 'Estudios Internacionales',
        'TUR' => 'Turismo y Hospitalidad',
        'HUM' => 'Humanidades',
    ];

    public function run(): void
    {
        foreach (self::FACULTADES as $codigo => $nombre) {
            Facultad::create(['codigo' => $codigo, 'nombre' => $nombre, 'activo' => true]);
        }
    }
}

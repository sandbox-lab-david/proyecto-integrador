<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\CategoriaMateria;
use App\Models\Malla;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class MallaSeeder extends Seeder
{
    public const MALLAS = [
        'COMP' => ['version' => '2024', 'materias' => [
            ['MAT101', 'Cálculo I', 4, 'Básica', 1],
            ['COM101', 'Programación I', 4, 'Profesional', 1],
            ['GEN101', 'Comunicación Oral y Escrita', 3, 'General', 1],
            ['MAT201', 'Cálculo II', 4, 'Básica', 2],
            ['COM201', 'Programación II', 4, 'Profesional', 2],
            ['FIS101', 'Física I', 4, 'Básica', 2],
            ['COM301', 'Estructuras de Datos', 4, 'Profesional', 3],
            ['COM302', 'Bases de Datos', 4, 'Profesional', 3],
            ['COM401', 'Ingeniería de Software', 4, 'Profesional', 4],
            ['COM402', 'Redes de Computadoras', 4, 'Profesional', 4],
        ]],
        'MED' => ['version' => '2023', 'materias' => [
            ['MED101', 'Anatomía I', 5, 'Básica', 1],
            ['MED102', 'Bioquímica', 4, 'Básica', 1],
            ['MED201', 'Fisiología', 5, 'Profesional', 2],
            ['MED202', 'Histología', 4, 'Profesional', 2],
        ]],
    ];

    public function run(): void
    {
        $categorias = CategoriaMateria::pluck('id', 'nombre');

        foreach (self::MALLAS as $codigoCarrera => $datos) {
            $carrera = Carrera::where('codigo', $codigoCarrera)->firstOrFail();
            $malla = Malla::create(['carrera_id' => $carrera->id, 'version' => $datos['version'], 'vigente' => true]);

            foreach ($datos['materias'] as [$codigo, $nombre, $creditos, $categoria, $nivel]) {
                $materia = Materia::create([
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'creditos' => $creditos,
                    'categoria_materia_id' => $categorias[$categoria] ?? null,
                    'activo' => true,
                ]);
                $malla->materias()->attach($materia->id, ['nivel' => $nivel]);
            }
        }
    }
}

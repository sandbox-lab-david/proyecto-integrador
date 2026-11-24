<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    public const SIN_TERCER_REGISTRO = ['MED', 'ENF'];

    public const CARRERAS = [
        'ING' => [
            'CIV' => 'Ingeniería Civil',
            'COMP' => 'Ingeniería en Computación',
            'AMB' => 'Ingeniería Ambiental',
            'BIO' => 'Ingeniería en Biodiversidad y Recursos Genéticos',
            'IND' => 'Ingeniería Industrial',
        ],
        'ARQ' => [
            'ARQ' => 'Arquitectura',
            'ARQI' => 'Arquitectura de Interiores',
            'ARQU' => 'Arquitectura y Urbanismo',
        ],
        'COM' => [
            'COMU' => 'Comunicación',
            'DGI' => 'Diseño Gráfico Interactivo',
            'PER' => 'Periodismo',
            'MKT' => 'Marketing y Transmedia',
            'PMC' => 'Producción para Medios de Comunicación',
            'GMC' => 'Gestión de Medios y Contenidos',
        ],
        'SAL' => [
            'MED' => 'Medicina',
            'ODO' => 'Odontología',
            'NUT' => 'Nutrición y Dietética',
            'ENF' => 'Enfermería',
        ],
        'DER' => [
            'DER' => 'Derecho',
            'CPOL' => 'Ciencias Políticas',
        ],
        'ENE' => [
            'NDIG' => 'Negocios Digitales',
            'ECO' => 'Economía',
            'ADM' => 'Administración de Empresas',
            'CEXT' => 'Comercio Exterior',
            'FIN' => 'Finanzas',
            'CAU' => 'Contabilidad y Auditoría',
        ],
        'EIN' => [
            'NINT' => 'Negocios Internacionales',
            'ARH' => 'Administración Internacional de Recursos Humanos',
        ],
        'TUR' => [
            'TUR' => 'Turismo',
            'NENT' => 'Negocios de Entretenimiento',
            'NTI' => 'Negocios Turísticos Internacionales',
        ],
        'HUM' => [
            'PSC' => 'Psicología Clínica',
            'PSO' => 'Psicología Organizacional',
            'EDU' => 'Educación',
            'DAN' => 'Danza',
            'AMU' => 'Artes Musicales',
            'PMU' => 'Producción Musical',
            'TMU' => 'Teatro Musical',
        ],
    ];

    public function run(): void
    {
        foreach (self::CARRERAS as $codigoFacultad => $carreras) {
            $facultad = Facultad::where('codigo', $codigoFacultad)->firstOrFail();

            foreach ($carreras as $codigo => $nombre) {
                Carrera::create([
                    'facultad_id' => $facultad->id,
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'nivel' => 'grado',
                    'admite_tercer_registro' => ! in_array($codigo, self::SIN_TERCER_REGISTRO, true),
                    'activo' => true,
                ]);
            }
        }
    }
}

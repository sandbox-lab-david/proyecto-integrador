<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoModuloTresSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['ENVIADO', 'Enviado por estudiante'],
            ['REVISADO', 'Revisado'],
            ['APTO_CONSEJO', 'Apto para consejo'],
            ['AGENDADO', 'Agendado'],
            ['APROBADO_EJECUCION', 'Aprobado para ejecución'],
            ['EJECUTADO', 'Ejecutado'],
            ['OBSERVADO', 'Observado'],
            ['RECHAZADO', 'Rechazado'],
        ];
        $nombres = [
            ['Ana Belén Cedeño', 'Carlos Mendoza', 'Daniela Zambrano'],
            ['José Luis Vera', 'María Fernanda Ponce', 'Pedro Alcívar'],
            ['Camila Macías', 'Diego Cárdenas', 'Valentina Loor'],
        ];
        $carreras = ['Ingeniería en Software', 'Ingeniería Civil', 'Ingeniería Industrial'];
        $tramites = ['Examen de recuperación', 'Tercera matrícula', 'Cambio de carrera'];

        foreach ($estados as $estadoIndex => [$slug, $estado]) {
            $agendaId = null;
            if (in_array($estado, ['Agendado', 'Aprobado para ejecución', 'Ejecutado'], true)) {
                $agendaNumero = 'DEMO-M3-'.str_replace(' ', '-', strtoupper($slug));
                DB::table('agendas_consejo_m3')->updateOrInsert(
                    ['numero' => $agendaNumero],
                    [
                        'fecha_sesion' => now()->addDays(7 + $estadoIndex)->toDateString(),
                        'estado' => 'Agendado',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $agendaId = DB::table('agendas_consejo_m3')->where('numero', $agendaNumero)->value('id');
            }

            for ($caso = 1; $caso <= 3; $caso++) {
                $codigo = sprintf('SOL-DEMO-M3-%s-%02d', $slug, $caso);
                DB::table('solicitudes_revision')->insertOrIgnore([
                    'codigo_solicitud' => $codigo,
                    'estudiante' => $nombres[$caso - 1][$estadoIndex % 3],
                    'codigo_siac' => sprintf('U2026D%03d', ($estadoIndex * 3) + $caso),
                    'carrera' => $carreras[($estadoIndex + $caso - 1) % count($carreras)],
                    'tramite' => $tramites[($estadoIndex + $caso - 1) % count($tramites)],
                    'materia' => $caso === 1 ? 'Matemáticas II' : null,
                    'motivo' => 'Registro de demostración para mostrar el flujo de solicitudes en estado: '.$estado.'.',
                    'estado' => $estado,
                    'created_at' => now()->subDays($estadoIndex + $caso),
                    'updated_at' => now(),
                ]);

                $solicitudId = DB::table('solicitudes_revision')->where('codigo_solicitud', $codigo)->value('id');
                if ($agendaId) {
                    DB::table('agenda_solicitud_m3')->insertOrIgnore([
                        'agenda_id' => $agendaId,
                        'solicitud_revision_id' => $solicitudId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (in_array($estado, ['Aprobado para ejecución', 'Ejecutado'], true)) {
                        DB::table('resoluciones_m3')->insertOrIgnore([
                            'agenda_id' => $agendaId,
                            'solicitud_revision_id' => $solicitudId,
                            'numero_resolucion' => 'RES-DEMO-M3-'.$slug.'-'.$caso,
                            'decision' => 'Aprobado',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}

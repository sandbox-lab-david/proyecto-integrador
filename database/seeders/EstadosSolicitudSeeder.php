<?php

namespace Database\Seeders;

use App\Support\Estados;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $labels = [Estados::BORRADOR => 'Borrador', Estados::ENVIADA => 'Enviado por estudiante', Estados::REVISADA => 'Revisado', Estados::OBSERVADA => 'Observado', Estados::APTA_CONSEJO => 'Apto para consejo', Estados::AGENDADA => 'Agendado', Estados::APROBADA_EJECUCION => 'Aprobado para ejecución', Estados::EJECUTADA => 'Ejecutado', Estados::RECHAZADA => 'Rechazado'];
        foreach ($labels as $code => $name) {
            DB::table('estados_solicitud')->insertOrIgnore(['codigo' => $code, 'nombre' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
        $transitions = [[Estados::BORRADOR, Estados::ENVIADA], [Estados::ENVIADA, Estados::REVISADA], [Estados::ENVIADA, Estados::OBSERVADA], [Estados::ENVIADA, Estados::RECHAZADA], [Estados::OBSERVADA, Estados::ENVIADA], [Estados::REVISADA, Estados::APTA_CONSEJO], [Estados::REVISADA, Estados::RECHAZADA], [Estados::APTA_CONSEJO, Estados::AGENDADA], [Estados::APTA_CONSEJO, Estados::RECHAZADA], [Estados::AGENDADA, Estados::APROBADA_EJECUCION], [Estados::AGENDADA, Estados::RECHAZADA], [Estados::APROBADA_EJECUCION, Estados::EJECUTADA]];
        foreach ($transitions as [$from,$to]) {
            DB::table('transiciones_estado')->updateOrInsert(['estado_origen' => $from, 'estado_destino' => $to], ['activa' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}

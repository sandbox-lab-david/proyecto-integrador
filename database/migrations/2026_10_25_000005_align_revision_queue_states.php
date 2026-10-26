<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('solicitudes_revision') || ! Schema::hasColumn('solicitudes_revision', 'estado')) {
            return;
        }

        foreach ([
            'Enviado' => 'Enviado por estudiante',
            'Enviado por Estudiante' => 'Enviado por estudiante',
            'Pendiente Director' => 'Revisado',
            'Aprobado' => 'Apto para consejo',
        ] as $anterior => $nuevo) {
            DB::table('solicitudes_revision')->where('estado', $anterior)->update([
                'estado' => $nuevo,
                'updated_at' => now(),
            ]);
        }

        Schema::table('solicitudes_revision', function (Blueprint $table): void {
            $table->string('estado')->default('Enviado por estudiante')->change();
        });
    }

    public function down(): void
    {
        // Keep the persisted labels aligned with the current review workflow.
    }
};

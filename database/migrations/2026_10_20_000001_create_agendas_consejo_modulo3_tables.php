<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('solicitudes_revision', 'observacion_director')) {
            Schema::table('solicitudes_revision', function (Blueprint $table) {
                $table->text('observacion_director')->nullable();
            });
        }
        if (! Schema::hasTable('agendas_consejo_m3')) {
            Schema::create('agendas_consejo_m3', function (Blueprint $table) {
                $table->id();
                $table->string('numero')->unique();
                $table->date('fecha_sesion');
                $table->string('estado')->default('Agendado');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('agenda_solicitud_m3')) {
            Schema::create('agenda_solicitud_m3', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agenda_id')->constrained('agendas_consejo_m3')->cascadeOnDelete();
                $table->foreignId('solicitud_revision_id')->constrained('solicitudes_revision')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['agenda_id', 'solicitud_revision_id']);
            });
        }
    }

    public function down(): void
    {
        // Estas tablas/columna pueden haber sido creadas antes de Laravel;
        // no se eliminan automáticamente para proteger agendas existentes.
    }
};

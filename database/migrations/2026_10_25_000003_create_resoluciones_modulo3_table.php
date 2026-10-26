<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('resoluciones_m3')) {
            return;
        }

        Schema::create('resoluciones_m3', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas_consejo_m3')->cascadeOnDelete();
            $table->foreignId('solicitud_revision_id')->unique()->constrained('solicitudes_revision')->cascadeOnDelete();
            $table->string('numero_resolucion')->unique();
            $table->string('decision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resoluciones_m3');
    }
};

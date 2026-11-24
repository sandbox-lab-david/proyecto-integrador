<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resoluciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->unsignedBigInteger('corte_agenda_id')->nullable();
            $table->string('numero_resolucion')->unique();
            $table->date('fecha_resolucion');
            $table->string('decision'); // Aprobado / Reprobado
            $table->text('observacion')->nullable();
            $table->string('documento_pdf')->nullable();
            $table->unsignedBigInteger('registrada_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resoluciones');
    }
};
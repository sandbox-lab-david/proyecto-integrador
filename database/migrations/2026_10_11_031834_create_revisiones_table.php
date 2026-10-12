<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->unsignedBigInteger('trabajador_id'); // Asistente o Director
            $table->boolean('documento_legible')->default(false);
            $table->boolean('datos_coinciden')->default(false);
            $table->boolean('requisitos_completos')->default(false);
            $table->text('observaciones_internas')->nullable();
            $table->string('decision'); // Aplica / Requiere correccion / Rechazada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisiones');
    }
};
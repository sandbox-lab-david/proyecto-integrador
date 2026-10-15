<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('respaldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();

            // documentos_requeridos es de G4: la llave foránea se agrega cuando exista.
            $table->foreignId('documento_requerido_id')->nullable();

            $table->string('nombre_original', 255);
            $table->string('ruta', 255);
            $table->string('mime', 100);
            $table->unsignedInteger('tamano_bytes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respaldos');
    }
};

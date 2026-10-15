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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();

            // Tablas de G1, G4 y G3 que todavía no están en develop: la columna queda lista
            // y la llave foránea se agrega en una migración posterior, cuando existan.
            $table->foreignId('carrera_estudiante_id');
            $table->foreignId('tipo_tramite_id');
            $table->foreignId('periodo_id');
            $table->foreignId('estado_solicitud_id');

            // El diccionario pide JSONB en PostgreSQL; en MySQL y SQLite queda como JSON.
            $table->jsonb('detalle')->nullable();
            $table->decimal('gpa_declarado', 5, 2)->nullable();
            $table->boolean('gpa_verificado')->default(false);
            $table->date('fecha_ultima_recuperacion')->nullable();
            $table->boolean('declaracion_veracidad');
            $table->string('codigo_sistema', 30)->nullable();
            $table->timestamp('enviada_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};

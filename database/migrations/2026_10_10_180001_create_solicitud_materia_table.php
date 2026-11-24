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
        Schema::create('solicitud_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();

            // cursos y trabajadores son de G1: la llave foránea se agrega cuando existan.
            $table->foreignId('curso_id')->nullable();
            $table->string('materia_externa', 255)->nullable();
            $table->string('institucion_externa', 255)->nullable();

            $table->decimal('nota_declarada', 5, 2)->nullable();
            $table->decimal('asistencia_declarada', 5, 2)->nullable();
            // Mismos valores que App\Enums\EstadoMateria.
            $table->enum('estado_declarado', ['aprobada', 'reprobada', 'semestre_actual', 'retirada'])->nullable();
            $table->unsignedTinyInteger('intento_declarado')->nullable();

            $table->boolean('verificado')->default(false);
            $table->foreignId('verificado_por')->nullable();
            $table->timestamp('verificado_at')->nullable();
            $table->string('observacion_verificacion', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_materia');
    }
};

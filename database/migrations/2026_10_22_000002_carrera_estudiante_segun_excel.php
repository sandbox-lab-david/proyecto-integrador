<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrera_estudiante', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('carrera_id');
            $table->dropConstrainedForeignId('periodo_id');
            $table->dropColumn('fechas');
        });

        Schema::table('carrera_estudiante', function (Blueprint $table) {
            $table->unsignedBigInteger('estudiante_id')->nullable(false)->change();
            $table->unsignedBigInteger('carrera_modalidad_id')->nullable(false)->change();
            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('carrera_modalidad_id')->references('id')->on('carrera_modalidad');
            $table->foreign('malla_id')->references('id')->on('mallas')->nullOnDelete();
            $table->date('fecha_inscripcion')->nullable();
            $table->date('fecha_fin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('carrera_estudiante', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->dropForeign(['carrera_modalidad_id']);
            $table->dropForeign(['malla_id']);
            $table->dropColumn(['fecha_inscripcion', 'fecha_fin']);
        });

        Schema::table('carrera_estudiante', function (Blueprint $table) {
            $table->unsignedBigInteger('estudiante_id')->nullable()->change();
            $table->unsignedBigInteger('carrera_modalidad_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->cascadeOnDelete();
            $table->foreignId('periodo_id')->nullable()->constrained('periodos')->nullOnDelete();
            $table->date('fechas')->nullable();
        });
    }
};

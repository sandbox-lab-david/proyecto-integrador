<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facultad_trabajador', function (Blueprint $table) {
            $table->foreignId('trabajador_id')->constrained('trabajadores')->cascadeOnDelete();
            $table->foreignId('facultad_id')->constrained('facultades')->cascadeOnDelete();
            $table->unique(['trabajador_id', 'facultad_id']);
        });

        Schema::table('malla_materia', function (Blueprint $table) {
            $table->foreignId('malla_id')->constrained('mallas')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->unsignedTinyInteger('nivel')->nullable();
            $table->unique(['malla_id', 'materia_id']);
        });
    }

    public function down(): void
    {
        Schema::table('malla_materia', function (Blueprint $table) {
            $table->dropUnique(['malla_id', 'materia_id']);
            $table->dropConstrainedForeignId('malla_id');
            $table->dropConstrainedForeignId('materia_id');
            $table->dropColumn('nivel');
        });

        Schema::table('facultad_trabajador', function (Blueprint $table) {
            $table->dropUnique(['trabajador_id', 'facultad_id']);
            $table->dropConstrainedForeignId('trabajador_id');
            $table->dropConstrainedForeignId('facultad_id');
        });
    }
};

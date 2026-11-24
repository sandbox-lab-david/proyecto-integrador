<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conservar las asignaciones existentes por código al introducir la relación con roles.
        Schema::table('rol_trabajador', function (Blueprint $table) {
            if (!Schema::hasColumn('rol_trabajador', 'rol_id')) {
                $table->unsignedBigInteger('rol_id')->nullable();
            }
        });

        // 2. Ajustar tabla carrera_estudiante
        Schema::table('carrera_estudiante', function (Blueprint $table) {
            if (!Schema::hasColumn('carrera_estudiante', 'estudiante_id')) {
                $table->unsignedBigInteger('estudiante_id')->after('user_id')->nullable();
            }
            if (!Schema::hasColumn('carrera_estudiante', 'carrera_modalidad_id')) {
                $table->unsignedBigInteger('carrera_modalidad_id')->nullable();
            }
            if (!Schema::hasColumn('carrera_estudiante', 'malla_id')) {
                $table->unsignedBigInteger('malla_id')->nullable();
            }
            if (!Schema::hasColumn('carrera_estudiante', 'fechas')) {
                $table->date('fechas')->nullable();
            }
            if (!Schema::hasColumn('carrera_estudiante', 'estado')) {
                $table->string('estado')->default('activo');
            }
        });

        // 3. Ajustar tabla materias (Soluciona el error en /cursos/create)
        Schema::table('materias', function (Blueprint $table) {
            if (!Schema::hasColumn('materias', 'categoria_materia_id')) {
                $table->unsignedBigInteger('categoria_materia_id')->nullable();
            }
            if (!Schema::hasColumn('materias', 'activo')) {
                $table->boolean('activo')->default(true);
            }
        });
    }

    public function down(): void
    {
        // Rollback opcional si se requiere
    }
};

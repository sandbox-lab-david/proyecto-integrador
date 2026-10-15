<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajustar tabla rol_trabajador (Llave compuesta trabajador_id + rol_id)
        Schema::table('rol_trabajador', function (Blueprint $table) {
            if (Schema::hasColumn('rol_trabajador', 'id')) {
                $table->dropColumn('id');
            }
            if (Schema::hasColumn('rol', 'rol_trabajador')) {
                $table->dropColumn('rol');
            }
            if (!Schema::hasColumn('rol_trabajador', 'rol_id')) {
                $table->unsignedBigInteger('rol_id');
            }
            // Definir llave primaria compuesta
            $table->primary(['trabajador_id', 'rol_id']);
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

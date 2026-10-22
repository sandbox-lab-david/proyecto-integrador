<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Completa bases que ejecutaron users antes de la actualización de G1. */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'tipo')) {
            Schema::table('users', function (Blueprint $table) {
                // Las cuentas anteriores reciben el tipo de menor privilegio.
                $table->enum('tipo', ['estudiante', 'trabajador'])->default('estudiante');
            });
        }

        if (! Schema::hasColumn('users', 'activo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('activo')->default(true);
            });
        }
    }

    public function down(): void
    {
        // Reparación aditiva: se conservan los datos. En instalaciones nuevas
        // estas columnas pertenecen a la migración original de users (G1).
    }
};

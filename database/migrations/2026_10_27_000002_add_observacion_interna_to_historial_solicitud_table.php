<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_solicitud', function (Blueprint $table) {
            $table->text('observacion_interna')->nullable();
        });

        DB::table('historial_solicitud')
            ->where('visible_estudiante', false)
            ->whereNotNull('comentario')
            ->update([
                'observacion_interna' => DB::raw('comentario'),
                'comentario' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('historial_solicitud', function (Blueprint $table) {
            $table->dropColumn('observacion_interna');
        });
    }
};

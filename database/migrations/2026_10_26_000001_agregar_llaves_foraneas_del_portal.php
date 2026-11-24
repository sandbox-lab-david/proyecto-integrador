<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Llaves foráneas de las tablas de G2 hacia tablas de G1, G3 y G4, tal como
 * las pide el diccionario de datos de modelos.xlsx. Las columnas se crearon
 * sin restricción porque esas tablas todavía no estaban en develop; esta
 * migración va después de todas ellas.
 *
 * Ninguna borra en cascada: no se puede borrar una inscripción, periodo,
 * trámite, estado, curso, trabajador o documento requerido que una solicitud use.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->foreign('carrera_estudiante_id')->references('id')->on('carrera_estudiante');
            $table->foreign('tipo_tramite_id')->references('id')->on('tipos_tramite');
            $table->foreign('periodo_id')->references('id')->on('periodos');
            $table->foreign('estado_solicitud_id')->references('id')->on('estados_solicitud');
        });

        Schema::table('solicitud_materia', function (Blueprint $table) {
            $table->foreign('curso_id')->references('id')->on('cursos');
            $table->foreign('verificado_por')->references('id')->on('trabajadores');
        });

        Schema::table('respaldos', function (Blueprint $table) {
            $table->foreign('documento_requerido_id')->references('id')->on('documentos_requeridos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respaldos', function (Blueprint $table) {
            $table->dropForeign(['documento_requerido_id']);
        });

        Schema::table('solicitud_materia', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);
            $table->dropForeign(['verificado_por']);
        });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropForeign(['carrera_estudiante_id']);
            $table->dropForeign(['tipo_tramite_id']);
            $table->dropForeign(['periodo_id']);
            $table->dropForeign(['estado_solicitud_id']);
        });
    }
};

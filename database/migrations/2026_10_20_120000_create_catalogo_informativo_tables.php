<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_solicitud', fn (Blueprint $table) => $table->boolean('visible_estudiante')->default(false));
        Schema::create('tipos_tramite', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->json('contenido');
            $table->timestamps();
        });
        Schema::create('documentos_requeridos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')->constrained('tipos_tramite');
            $table->string('codigo');
            $table->string('nombre');
            $table->string('obligacion');
            $table->json('contenido');
            $table->timestamps();
            $table->unique(['tipo_tramite_id', 'codigo']);
        });
        Schema::create('revisiones_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->json('contenido');
            $table->string('ruta_hechos');
            $table->boolean('activa')->default(false);
            $table->timestamps();
        });
        Schema::create('comprobaciones_informativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id')->index();
            $table->string('kb_version');
            $table->json('respuesta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobaciones_informativas');
        Schema::dropIfExists('revisiones_catalogo');
        Schema::dropIfExists('documentos_requeridos');
        Schema::dropIfExists('tipos_tramite');
        Schema::table('historial_solicitud', fn (Blueprint $table) => $table->dropColumn('visible_estudiante'));
    }
};

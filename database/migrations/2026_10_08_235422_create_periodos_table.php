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
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo',30)->unique();
            $table->string('nombre',255);
            $table->enum('tipo', ['ordinario', 'extraordinario', 'intensivo']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedTinyInteger('semana_primer_parcial');
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};

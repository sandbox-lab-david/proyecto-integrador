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
        Schema::create('registros_anteriores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_materia_id')->constrained('solicitud_materia')->cascadeOnDelete();
            $table->year('anio');
            $table->string('periodo_texto', 50);
            $table->decimal('nota', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_anteriores');
    }
};

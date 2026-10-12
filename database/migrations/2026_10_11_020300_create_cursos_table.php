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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignid('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignid('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->foreignid('profesor_id')->nullable()->constrained('trabajadores')->onDelete('cascade');
            $table->string('paralelo');
            $table->unsignedSmallInteger('cupo')->nullable();
            $table->unique(['materia_id', 'periodo_id', 'paralelo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};

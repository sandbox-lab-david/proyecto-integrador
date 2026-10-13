<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_agenda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facultad_id');
            $table->date('fecha_sesion');
            $table->text('asistentes')->nullable();
            $table->string('estado')->default('Abierto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_agenda');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puntos_internos_agenda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corte_agenda_id');
            $table->string('titulo');
            $table->text('descripcion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puntos_internos_agenda');
    }
};
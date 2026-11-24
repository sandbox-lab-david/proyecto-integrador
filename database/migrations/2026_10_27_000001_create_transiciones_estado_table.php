<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transiciones_estado')) {
            Schema::create('transiciones_estado', function (Blueprint $table) {
                $table->id();
                $table->string('estado_origen');
                $table->string('estado_destino');
                $table->boolean('activa')->default(true);
                $table->timestamps();
                $table->unique(['estado_origen', 'estado_destino']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transiciones_estado');
    }
};

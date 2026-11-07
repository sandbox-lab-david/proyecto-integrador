<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borradores_correccion_g2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->string('tramite', 60);
            $table->string('revision_base', 64);
            $table->json('contenido');
            $table->timestamps();
            $table->unique(['user_id', 'solicitud_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borradores_correccion_g2');
    }
};

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
        Schema::create('mensajes_chat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->enum('emisor', ['usuario', 'bot']);
            $table->text('texto');
            $table->string('intencion', 50)->nullable();
            $table->string('tramite_id', 50)->nullable();
            $table->unsignedBigInteger('tipo_tramite_id')->nullable();
            $table->boolean('resuelta')->nullable();
            $table->unsignedInteger('tiempo_ms')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes_chat');
    }
};

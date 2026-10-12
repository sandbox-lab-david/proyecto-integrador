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
        Schema::create('importacions', function (Blueprint $table) {
            $table->id();
            $table->string('entidad', 50);
            $table->string('archivo_ruta', 255);
            $table->foreignid('useri_id')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'procesado', 'completada', 'fallida']);
            $table->unsignedInteger('total_filas')->default(0);
            $table->unsignedInteger('creadas')->default(0);
            $table->unsignedInteger('actualizadas')->default(0);
            $table->unsignedInteger('rechazadas')->default(0);
            $table->json('errores')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacions');
    }
};

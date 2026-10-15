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
        Schema::create('documentos_firmados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_generado_id')->unique()->constrained('documentos_generados');
            $table->string('ruta', 255);
            $table->unsignedInteger('tamano_bytes');
            $table->foreignId('subido_por')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_firmados');
    }
};

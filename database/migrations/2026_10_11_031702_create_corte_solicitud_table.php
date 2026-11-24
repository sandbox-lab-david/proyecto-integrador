<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corte_solicitud', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corte_agenda_id');
            $table->unsignedBigInteger('solicitud_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corte_solicitud');
    }
};
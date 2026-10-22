<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('director_carreras', 'directores_carrera');
    }

    public function down(): void
    {
        Schema::rename('directores_carrera', 'director_carreras');
    }
};

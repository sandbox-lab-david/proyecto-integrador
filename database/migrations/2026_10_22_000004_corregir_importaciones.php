<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('importaciones', function (Blueprint $table) {
            $table->renameColumn('useri_id', 'user_id');
        });

        $this->cambiarEstados(['pendiente', 'procesando', 'completada', 'fallida'], 'procesado', 'procesando');
    }

    public function down(): void
    {
        $this->cambiarEstados(['pendiente', 'procesado', 'completada', 'fallida'], 'procesando', 'procesado');

        Schema::table('importaciones', function (Blueprint $table) {
            $table->renameColumn('user_id', 'useri_id');
        });
    }

    private function cambiarEstados(array $estados, string $antes, string $despues): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE importaciones DROP CONSTRAINT IF EXISTS importaciones_estado_check');
            DB::table('importaciones')->where('estado', $antes)->update(['estado' => $despues]);
            $lista = collect($estados)->map(fn ($e) => "'$e'")->join(', ');
            DB::statement("ALTER TABLE importaciones ADD CONSTRAINT importaciones_estado_check CHECK (estado IN ($lista))");
            DB::statement("ALTER TABLE importaciones ALTER COLUMN estado SET DEFAULT 'pendiente'");

            return;
        }

        Schema::table('importaciones', function (Blueprint $table) {
            $table->string('estado', 20)->default('pendiente')->change();
        });
        DB::table('importaciones')->where('estado', $antes)->update(['estado' => $despues]);
        Schema::table('importaciones', function (Blueprint $table) use ($estados) {
            $table->enum('estado', $estados)->default('pendiente')->change();
        });
    }
};

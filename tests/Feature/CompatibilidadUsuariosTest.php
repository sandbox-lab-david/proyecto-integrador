<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompatibilidadUsuariosTest extends TestCase
{
    private function migracion(): \Illuminate\Database\Migrations\Migration
    {
        return require database_path('migrations/2026_10_21_230000_completar_tipo_y_activo_en_users.php');
    }

    public function test_completa_base_antigua_sin_borrar_usuarios_y_admite_registros_nuevos(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
        });
        DB::table('users')->insert(['name' => 'Cuenta anterior', 'email' => 'anterior@example.test', 'password' => 'hash-anterior']);
        $migracion = $this->migracion();
        $migracion->up();
        $migracion->up();
        $this->assertDatabaseHas('users', ['email' => 'anterior@example.test', 'password' => 'hash-anterior', 'tipo' => 'estudiante', 'activo' => true]);
        DB::table('users')->insert(['name' => 'Cuenta nueva', 'email' => 'nueva@example.test', 'password' => 'hash-nuevo', 'tipo' => 'estudiante']);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['email' => 'nueva@example.test', 'activo' => true]);
    }

    public function test_respeta_columnas_y_valores_que_ya_estan_actualizados(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['estudiante', 'trabajador']);
            $table->boolean('activo')->default(true);
        });
        DB::table('users')->insert(['tipo' => 'trabajador', 'activo' => false]);
        $migracion = $this->migracion();
        $migracion->up();
        $migracion->down();
        $this->assertDatabaseHas('users', ['tipo' => 'trabajador', 'activo' => false]);
    }
}

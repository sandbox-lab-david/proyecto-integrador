<?php

use App\Models\TipoTramite;
use App\Services\Catalogo\CatalogoTramites;
use App\Services\Catalogo\NormalizadorCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_tramite', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
        Schema::table('tipos_tramite', function (Blueprint $table) {
            $table->foreignId('categoria_tramite_id')->nullable()->constrained('categorias_tramite')->restrictOnDelete();
            $table->boolean('catalogo_normalizado')->default(false);
        });
        Schema::create('requisitos_tramite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')->constrained('tipos_tramite')->cascadeOnDelete();
            $table->text('texto');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
        Schema::create('campos_tramite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')->constrained('tipos_tramite')->cascadeOnDelete();
            $table->string('ambito', 20);
            $table->string('nombre');
            $table->string('etiqueta');
            $table->string('tipo', 30);
            $table->boolean('obligatorio');
            $table->unsignedInteger('orden')->default(0);
            $table->json('configuracion');
            $table->timestamps();
            $table->unique(['tipo_tramite_id', 'ambito', 'nombre']);
        });
        Schema::create('umbrales_tramite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')->constrained('tipos_tramite')->cascadeOnDelete();
            $table->string('codigo');
            $table->double('valor');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
            $table->unique(['tipo_tramite_id', 'codigo']);
        });
        Schema::create('facultad_tipo_tramite', function (Blueprint $table) {
            $table->foreignId('facultad_id')->constrained('facultades')->restrictOnDelete();
            $table->foreignId('tipo_tramite_id')->constrained('tipos_tramite')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['facultad_id', 'tipo_tramite_id']);
        });
        Schema::create('palabras_clave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')->constrained('tipos_tramite')->cascadeOnDelete();
            $table->string('expresion');
            $table->timestamps();
            $table->unique(['tipo_tramite_id', 'expresion']);
        });

        DB::transaction(function () {
            $normalizador = new NormalizadorCatalogo;
            $normalizador->prepararCategorias();
            TipoTramite::orderBy('id')->each(fn ($tipo) => $normalizador->importar($tipo));
        });
    }

    public function down(): void
    {
        // Conserva las ediciones normalizadas en el formato que entiende la versión anterior.
        DB::transaction(function () {
            $catalogo = new CatalogoTramites;
            TipoTramite::where('catalogo_normalizado', true)->each(function ($tipo) use ($catalogo) {
                $tipo->update(['contenido' => $catalogo->contenidoBorrador($tipo)]);
            });
        });
        Schema::dropIfExists('palabras_clave');
        Schema::dropIfExists('facultad_tipo_tramite');
        Schema::dropIfExists('umbrales_tramite');
        Schema::dropIfExists('campos_tramite');
        Schema::dropIfExists('requisitos_tramite');
        $quitarColumnas = fn () => Schema::table('tipos_tramite', function (Blueprint $table) {
            $table->dropForeign(['categoria_tramite_id']);
            $table->dropColumn(['categoria_tramite_id', 'catalogo_normalizado']);
        });
        // SQLite reconstruye la tabla al quitar la FK; conserva sus referencias externas.
        if (DB::getDriverName() === 'sqlite') {
            Schema::withoutForeignKeyConstraints($quitarColumnas);
        } else {
            $quitarColumnas();
        }
        Schema::dropIfExists('categorias_tramite');
    }
};

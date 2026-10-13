<?php

namespace Tests\Feature;

use App\Enums\EstadoMateria;
use App\Models\DocumentoFirmado;
use App\Models\DocumentoGenerado;
use App\Models\RegistroAnterior;
use App\Models\Respaldo;
use App\Models\Solicitud;
use App\Models\SolicitudMateria;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModeloSolicitudesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Las tablas de G1, G3 y G4 todavía no existen, así que las llaves hacia
     * ellas se pasan a mano. Cuando publiquen sus factories esto sobra.
     */
    private function solicitud(array $atributos = []): Solicitud
    {
        return Solicitud::factory()->create($atributos + $this->llavesAjenas());
    }

    private function llavesAjenas(): array
    {
        return [
            'carrera_estudiante_id' => 1,
            'tipo_tramite_id' => 1,
            'periodo_id' => 1,
            'estado_solicitud_id' => 1,
        ];
    }

    public function test_las_siete_tablas_tienen_las_columnas_del_diccionario(): void
    {
        $tablas = [
            'solicitudes' => [
                'id', 'codigo', 'carrera_estudiante_id', 'tipo_tramite_id', 'periodo_id', 'estado_solicitud_id',
                'detalle', 'gpa_declarado', 'gpa_verificado', 'fecha_ultima_recuperacion', 'declaracion_veracidad',
                'codigo_sistema', 'enviada_at', 'created_at', 'updated_at',
            ],
            'solicitud_materia' => [
                'id', 'solicitud_id', 'curso_id', 'materia_externa', 'institucion_externa', 'nota_declarada',
                'asistencia_declarada', 'estado_declarado', 'intento_declarado', 'verificado', 'verificado_por',
                'verificado_at', 'observacion_verificacion', 'created_at', 'updated_at',
            ],
            'registros_anteriores' => [
                'id', 'solicitud_materia_id', 'anio', 'periodo_texto', 'nota', 'created_at', 'updated_at',
            ],
            'respaldos' => [
                'id', 'solicitud_id', 'documento_requerido_id', 'nombre_original', 'ruta', 'mime', 'tamano_bytes',
                'created_at', 'updated_at',
            ],
            'documentos_generados' => [
                'id', 'solicitud_id', 'version', 'ruta_docx', 'ruta_pdf', 'created_at', 'updated_at',
            ],
            'documentos_firmados' => [
                'id', 'documento_generado_id', 'ruta', 'tamano_bytes', 'subido_por', 'created_at', 'updated_at',
            ],
            'notifications' => [
                'id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at',
            ],
        ];

        foreach ($tablas as $tabla => $columnas) {
            $this->assertEqualsCanonicalizing($columnas, Schema::getColumnListing($tabla), "Columnas de {$tabla}");
        }
    }

    public function test_la_solicitud_convierte_sus_columnas_al_leerlas(): void
    {
        $solicitud = $this->solicitud([
            'detalle' => ['motivo' => 'Calamidad doméstica', 'parcial' => 'primer_parcial'],
            'gpa_declarado' => 82.5,
            'fecha_ultima_recuperacion' => '2025-03-15',
        ])->fresh();

        // assertEquals: JSONB no conserva el orden de las claves.
        $this->assertEquals(['motivo' => 'Calamidad doméstica', 'parcial' => 'primer_parcial'], $solicitud->detalle);
        $this->assertSame('82.50', $solicitud->gpa_declarado);
        $this->assertInstanceOf(Carbon::class, $solicitud->fecha_ultima_recuperacion);
        $this->assertSame('2025-03-15', $solicitud->fecha_ultima_recuperacion->toDateString());
        $this->assertTrue($solicitud->declaracion_veracidad);
        $this->assertFalse($solicitud->gpa_verificado);
        $this->assertNull($solicitud->enviada_at);
        $this->assertMatchesRegularExpression('/^SOL-\d{4}-\d{5}$/', $solicitud->codigo);
    }

    public function test_enviada_registra_la_fecha_de_envio(): void
    {
        $solicitud = Solicitud::factory()->enviada()->create($this->llavesAjenas());

        $this->assertInstanceOf(Carbon::class, $solicitud->fresh()->enviada_at);
    }

    public function test_el_codigo_de_la_solicitud_no_se_repite(): void
    {
        $this->solicitud(['codigo' => 'SOL-2026-00001']);

        $this->expectException(QueryException::class);
        $this->solicitud(['codigo' => 'SOL-2026-00001']);
    }

    public function test_la_asignacion_masiva_no_toca_las_columnas_reservadas_a_los_servicios(): void
    {
        $solicitud = $this->solicitud();
        $solicitud->fill(['gpa_verificado' => true, 'codigo_sistema' => 'CAFI-001', 'gpa_declarado' => 90]);

        $this->assertFalse($solicitud->gpa_verificado);
        $this->assertNull($solicitud->codigo_sistema);
        $this->assertSame('90.00', $solicitud->gpa_declarado);

        $materia = SolicitudMateria::factory()->externa()->for($solicitud)->create();
        $materia->fill(['verificado' => true, 'verificado_por' => 7, 'nota_declarada' => 55]);

        $this->assertFalse($materia->verificado);
        $this->assertNull($materia->verificado_por);
        $this->assertSame('55.00', $materia->nota_declarada);
    }

    public function test_las_relaciones_propias_recorren_el_expediente(): void
    {
        $usuario = User::factory()->create();
        $solicitud = $this->solicitud();

        $materia = SolicitudMateria::factory()->externa()->for($solicitud)->create();
        RegistroAnterior::factory()->count(2)->for($materia)->create();
        Respaldo::factory()->count(2)->for($solicitud)->create();
        $documento = DocumentoGenerado::factory()->for($solicitud)->create();
        DocumentoFirmado::factory()->for($documento)->create(['subido_por' => $usuario->id]);

        $solicitud = Solicitud::with([
            'materias.registrosAnteriores', 'respaldos', 'documentosGenerados.documentoFirmado.usuario',
        ])->findOrFail($solicitud->id);

        $this->assertCount(1, $solicitud->materias);
        $this->assertCount(2, $solicitud->materias->first()->registrosAnteriores);
        $this->assertCount(2, $solicitud->respaldos);
        $this->assertCount(1, $solicitud->documentosGenerados);
        $this->assertTrue($solicitud->documentosGenerados->first()->documentoFirmado->usuario->is($usuario));

        $this->assertTrue($materia->solicitud->is($solicitud));
        $this->assertTrue($materia->registrosAnteriores->first()->solicitudMateria->is($materia));
        $this->assertTrue($solicitud->respaldos->first()->solicitud->is($solicitud));
        $this->assertTrue($documento->documentoFirmado->documentoGenerado->is($documento));
    }

    public function test_la_materia_guarda_cada_estado_del_enum(): void
    {
        $solicitud = $this->solicitud();

        foreach (EstadoMateria::cases() as $estado) {
            $materia = SolicitudMateria::factory()->externa()->for($solicitud)->create([
                'estado_declarado' => $estado,
            ]);

            $this->assertSame($estado, $materia->fresh()->estado_declarado);
            $this->assertDatabaseHas('solicitud_materia', ['id' => $materia->id, 'estado_declarado' => $estado->value]);
        }
    }

    public function test_la_materia_externa_no_tiene_curso(): void
    {
        $materia = SolicitudMateria::factory()->externa()->for($this->solicitud())->create()->fresh();

        $this->assertNull($materia->curso_id);
        $this->assertNotNull($materia->materia_externa);
        $this->assertNotNull($materia->institucion_externa);
        $this->assertFalse($materia->verificado);
    }

    public function test_borrar_la_solicitud_borra_su_expediente(): void
    {
        $solicitud = $this->solicitud();
        $materia = SolicitudMateria::factory()->externa()->for($solicitud)->create();
        RegistroAnterior::factory()->count(2)->for($materia)->create();
        Respaldo::factory()->for($solicitud)->create();
        DocumentoGenerado::factory()->for($solicitud)->create();

        $solicitud->delete();

        $this->assertDatabaseCount('solicitud_materia', 0);
        $this->assertDatabaseCount('registros_anteriores', 0);
        $this->assertDatabaseCount('respaldos', 0);
        $this->assertDatabaseCount('documentos_generados', 0);
    }

    public function test_no_se_puede_borrar_una_solicitud_con_documento_firmado(): void
    {
        $solicitud = $this->solicitud();
        $documento = DocumentoGenerado::factory()->for($solicitud)->create();
        DocumentoFirmado::factory()->for($documento)->create();

        try {
            $solicitud->delete();
            $this->fail('El documento firmado debía impedir el borrado.');
        } catch (QueryException) {
            $this->assertDatabaseHas('solicitudes', ['id' => $solicitud->id]);
            $this->assertDatabaseCount('documentos_firmados', 1);
        }
    }

    public function test_cada_version_del_documento_admite_una_sola_firma(): void
    {
        $documento = DocumentoGenerado::factory()->for($this->solicitud())->create();
        DocumentoFirmado::factory()->for($documento)->create();

        $this->expectException(QueryException::class);
        DocumentoFirmado::factory()->for($documento)->create();
    }

    public function test_la_tabla_de_notificaciones_alimenta_la_campana_del_usuario(): void
    {
        $usuario = User::factory()->create();

        $usuario->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'prueba',
            'data' => ['titulo' => 'Solicitud enviada', 'mensaje' => 'Tu solicitud fue recibida.', 'url' => '/'],
        ]);

        $this->assertCount(1, $usuario->unreadNotifications);
        $this->assertSame('Solicitud enviada', $usuario->unreadNotifications->first()->data['titulo']);
    }
}

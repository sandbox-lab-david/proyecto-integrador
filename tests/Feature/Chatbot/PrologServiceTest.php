<?php

namespace Tests\Feature\Chatbot;

use App\Models\CarreraEstudiante;
use App\Models\EstadoSolicitud;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Chatbot\PrologService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrologServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitud_real_usa_gpa_general_y_nunca_nota_de_materia(): void
    {
        $tipo = TipoTramite::create(['codigo' => 'examen_recuperacion', 'nombre' => 'Recuperación', 'slug' => 'examen-recuperacion', 'contenido' => []]);
<<<<<<< HEAD
        $s = Solicitud::factory()->create(['carrera_estudiante_id' => CarreraEstudiante::factory()->create()->id,
            'periodo_id' => Periodo::factory()->create()->id,
            'estado_solicitud_id' => EstadoSolicitud::firstOrCreate(['codigo' => 'enviada'], ['nombre' => 'Enviada'])->id,
            'tipo_tramite_id' => $tipo->id, 'gpa_declarado' => 72, 'fecha_ultima_recuperacion' => '2025-10-20',
            'detalle' => ['campos' => ['gpa_periodo' => 99], 'materias' => [['datos' => ['nota' => 98]]]]]);
=======
        $s = Solicitud::factory()->create(['carrera_estudiante_id' => CarreraEstudiante::factory()->create()->id, 'periodo_id' => Periodo::factory()->create()->id, 'estado_solicitud_id' => EstadoSolicitud::create(['codigo' => 'enviada', 'nombre' => 'Enviada'])->id, 'tipo_tramite_id' => $tipo->id, 'gpa_declarado' => 72, 'fecha_ultima_recuperacion' => '2025-10-20', 'detalle' => ['campos' => ['gpa_periodo' => 99], 'materias' => [['datos' => ['nota' => 98]]]]]);
>>>>>>> origin/develop
        $motor = $this->createMock(PrologClient::class);
        $motor->expects($this->once())->method('evaluar')->with($this->callback(fn ($p) => $p['version'] === 3 && $p['perfil'] === ['gpa_general' => 72.0, 'fecha_ultima_recuperacion' => '2025-10-20'] && $p['origen_datos'] === 'manual'))->willReturn(['version' => 3, 'kb_version' => 'catalogo-informativo-001', 'resultado' => null, 'comprobaciones' => []]);
        $this->app->instance(PrologClient::class, $motor);
        $r = app(PrologService::class)->evaluar($s, true, 'institucional', true);
        $this->assertNull($r['resultado']);
        $this->assertDatabaseCount('comprobaciones_informativas', 1);
    }

    public function test_gpa_ausente_no_se_sustituye_por_datos_historicos(): void
    {
        $s = new Solicitud(['gpa_declarado' => null, 'detalle' => ['tramite' => 'recuperacion', 'campos' => ['gpa_periodo' => 99], 'materias' => [['nota' => 98]]]]);
        $s->setRelation('tipoTramite', null);
        $motor = $this->createMock(PrologClient::class);
        $motor->expects($this->once())->method('evaluar')->with($this->callback(fn ($p) => $p['perfil'] === []))->willReturn(['resultado' => null]);
        $this->app->instance(PrologClient::class, $motor);
        app(PrologService::class)->evaluar($s);
    }

    public function test_alias_ambiguos_se_rechazan_antes_del_motor(): void
    {
        $this->expectException(ValidationException::class);
        app(PrologService::class)->evaluar('examen_recuperacion', ['promedio' => 72, 'gpa_general' => 90]);
    }

    public function test_tramite_inexistente_se_rechaza(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(PrologService::class)->evaluar('inventado');
    }

    public function test_campos_solo_son_gpa_general_y_fecha(): void
    {
        $this->assertSame(['gpa_general', 'fecha_ultima_recuperacion'], array_column(app(PrologService::class)->camposFormulario('examen_recuperacion'), 'nombre'));
        $this->assertSame([], app(PrologService::class)->camposFormulario('homologacion'));
    }
}

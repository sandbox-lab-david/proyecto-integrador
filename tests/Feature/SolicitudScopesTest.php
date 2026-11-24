<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Solicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Scopes publicados de Solicitud (contrato C42). Los datos de G1 salen de
 * sus factories; trámites y estados, del seeder del catálogo de G4.
 */
class SolicitudScopesTest extends TestCase
{
    use LlavesAjenasPrueba, RefreshDatabase;

    private function solicitud(array $atributos = []): Solicitud
    {
        return Solicitud::factory()->create($atributos + $this->llavesAjenas());
    }

    public function test_del_periodo_filtra_por_periodo(): void
    {
        [$periodo, $otro] = Periodo::factory()->count(2)->create()->modelKeys();
        $delPeriodo = $this->solicitud(['periodo_id' => $periodo]);
        $this->solicitud(['periodo_id' => $otro]);

        $this->assertEquals([$delPeriodo->id], Solicitud::delPeriodo($periodo)->pluck('id')->all());
    }

    public function test_del_tramite_filtra_por_tipo_de_tramite(): void
    {
        [$tramite, $otro] = [$this->tipoTramite('examen_gracia'), $this->tipoTramite('retiro_materia')];
        $delTramite = $this->solicitud(['tipo_tramite_id' => $tramite]);
        $this->solicitud(['tipo_tramite_id' => $otro]);

        $this->assertEquals([$delTramite->id], Solicitud::delTramite($tramite)->pluck('id')->all());
    }

    public function test_los_scopes_se_encadenan(): void
    {
        [$periodo, $otroPeriodo] = Periodo::factory()->count(2)->create()->modelKeys();
        [$tramite, $otroTramite] = [$this->tipoTramite('examen_gracia'), $this->tipoTramite('retiro_materia')];
        $coincide = $this->solicitud(['periodo_id' => $periodo, 'tipo_tramite_id' => $tramite]);
        $this->solicitud(['periodo_id' => $periodo, 'tipo_tramite_id' => $otroTramite]);
        $this->solicitud(['periodo_id' => $otroPeriodo, 'tipo_tramite_id' => $tramite]);

        $this->assertEquals([$coincide->id], Solicitud::delPeriodo($periodo)->delTramite($tramite)->pluck('id')->all());
    }

    public function test_buscar_sin_texto_no_filtra(): void
    {
        $this->solicitud();
        $this->solicitud();

        $this->assertSame(2, Solicitud::buscar(null)->count());
        $this->assertSame(2, Solicitud::buscar('')->count());
        $this->assertSame(2, Solicitud::buscar('   ')->count());
    }

    public function test_buscar_encuentra_por_codigo_de_solicitud_y_por_codigo_cafi(): void
    {
        $porCodigo = $this->solicitud(['codigo' => 'SOL-2026-00042']);
        $porCafi = $this->solicitud(['codigo' => 'SOL-2026-00043']);
        $porCafi->forceFill(['codigo_sistema' => 'CAFI-777'])->save();
        $this->solicitud(['codigo' => 'SOL-2026-00044']);

        $this->assertEquals([$porCodigo->id], Solicitud::buscar('00042')->pluck('id')->all());
        $this->assertEquals([$porCafi->id], Solicitud::buscar('cafi-777')->pluck('id')->all());
    }

    public function test_buscar_encuentra_por_nombres_apellidos_codigo_y_cedula_del_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create([
            'nombres' => 'Ximena Lucía', 'apellidos' => 'Quintana Vera',
            'codigo' => '2026123456', 'cedula' => '0912345678',
        ]);
        $suya = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion($estudiante)]);
        $this->solicitud();

        foreach (['Lucía', 'quintana', '2026123456', '0912345678'] as $texto) {
            $this->assertEquals([$suya->id], Solicitud::buscar($texto)->pluck('id')->all(), $texto);
        }
    }

    public function test_buscar_exige_todas_las_palabras_y_no_rompe_los_demas_filtros(): void
    {
        $periodo = Periodo::factory()->create()->id;
        $ximena = Estudiante::factory()->create(['nombres' => 'Ximena', 'apellidos' => 'Quintana']);
        $otraXimena = Estudiante::factory()->create(['nombres' => 'Ximena', 'apellidos' => 'Zambrano']);
        $coincide = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion($ximena), 'periodo_id' => $periodo]);
        $this->solicitud(['carrera_estudiante_id' => $this->inscripcion($ximena)]);
        $this->solicitud(['carrera_estudiante_id' => $this->inscripcion($otraXimena), 'periodo_id' => $periodo]);

        $this->assertSame(3, Solicitud::buscar('ximena')->count());
        $this->assertSame(2, Solicitud::buscar('Ximena Quintana')->count());
        // Las palabras se agrupan: el «o» entre columnas no se escapa de delPeriodo.
        $this->assertEquals([$coincide->id], Solicitud::delPeriodo($periodo)->buscar('Ximena Quintana')->pluck('id')->all());
    }

    public function test_del_estudiante_trae_solo_las_solicitudes_de_ese_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();
        // Dos inscripciones del mismo estudiante (cambio de carrera, por ejemplo).
        $primera = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion($estudiante)]);
        $segunda = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion($estudiante)]);
        $this->solicitud();

        $this->assertEqualsCanonicalizing(
            [$primera->id, $segunda->id],
            Solicitud::delEstudiante($estudiante->id)->pluck('id')->all()
        );
    }

    public function test_de_carrera_filtra_por_la_carrera_de_la_inscripcion(): void
    {
        $carrera = Carrera::factory()->create();
        // Misma carrera en dos modalidades: el filtro es por carrera, no por modalidad.
        $presencial = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion(carrera: $carrera)]);
        $enLinea = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion(carrera: $carrera, modalidad: 'En línea')]);
        $this->solicitud();

        $this->assertEqualsCanonicalizing(
            [$presencial->id, $enLinea->id],
            Solicitud::deCarrera($carrera->id)->pluck('id')->all()
        );
    }

    public function test_de_facultad_filtra_por_la_facultad_de_la_carrera(): void
    {
        $facultad = Facultad::factory()->create();
        [$una, $otra] = Carrera::factory()->count(2)->create(['facultad_id' => $facultad->id]);
        $deUna = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion(carrera: $una)]);
        $deOtra = $this->solicitud(['carrera_estudiante_id' => $this->inscripcion(carrera: $otra)]);
        $this->solicitud();

        $this->assertEqualsCanonicalizing(
            [$deUna->id, $deOtra->id],
            Solicitud::deFacultad($facultad->id)->pluck('id')->all()
        );
    }

    public function test_en_estado_acepta_un_codigo_o_varios(): void
    {
        $enviada = $this->solicitud(['estado_solicitud_id' => $this->estadoSolicitud('enviada')]);
        $borrador = $this->solicitud(['estado_solicitud_id' => $this->estadoSolicitud('borrador')]);
        $observada = $this->solicitud([
            'estado_solicitud_id' => $this->estadoSolicitud('observada'),
        ]);

        $this->assertEquals([$enviada->id], Solicitud::enEstado('enviada')->pluck('id')->all());
        $this->assertEqualsCanonicalizing(
            [$borrador->id, $observada->id],
            Solicitud::enEstado(['borrador', 'observada'])->pluck('id')->all()
        );
        $this->assertSame(0, Solicitud::enEstado([])->count());
    }

    /**
     * Inscripción nueva del estudiante en la carrera y modalidad indicadas,
     * o de un estudiante y una carrera nuevos cuando no se indican.
     */
    private function inscripcion(?Estudiante $estudiante = null, ?Carrera $carrera = null, string $modalidad = 'Presencial'): int
    {
        $carreraModalidad = CarreraModalidad::firstOrCreate([
            'carrera_id' => ($carrera ?? Carrera::factory()->create())->id,
            'modalidad_id' => Modalidad::firstOrCreate(['nombre' => $modalidad])->id,
        ]);

        return CarreraEstudiante::factory()->create([
            'estudiante_id' => $estudiante ?? Estudiante::factory(),
            'carrera_modalidad_id' => $carreraModalidad->id,
        ])->id;
    }
}

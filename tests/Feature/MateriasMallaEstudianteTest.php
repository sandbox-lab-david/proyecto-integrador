<?php

namespace Tests\Feature;

use App\Models\CarreraEstudiante;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Malla;
use App\Models\MallaMateria;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\User;
use App\Services\Solicitudes\MateriasSolicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RF-2.5: el selector ofrece los cursos de la malla del estudiante, con las factories de G1. */
class MateriasMallaEstudianteTest extends TestCase
{
    use RefreshDatabase;
    use RequisitosPrologPrueba;

    private Curso $deLaMalla;

    private Curso $fueraDeLaMalla;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $periodo = Periodo::factory()->create();
        $this->deLaMalla = Curso::factory()->create(['periodo_id' => $periodo->id]);
        $this->fueraDeLaMalla = Curso::factory()->create(['periodo_id' => $periodo->id]);
    }

    private function estudiante(?Malla $malla, string $estado = 'activo'): User
    {
        $usuario = User::factory()->create(['tipo' => 'estudiante', 'activo' => true]);
        $estudiante = Estudiante::factory()->create(['user_id' => $usuario->id]);
        CarreraEstudiante::factory()->create([
            'estudiante_id' => $estudiante->id, 'malla_id' => $malla?->id, 'estado' => $estado,
        ]);

        return $usuario;
    }

    private function malla(Materia ...$materias): Malla
    {
        $malla = Malla::factory()->vigente()->create();
        foreach ($materias as $materia) {
            MallaMateria::factory()->create(['malla_id' => $malla->id, 'materia_id' => $materia->id]);
        }

        return $malla;
    }

    private function ofrecidos(): array
    {
        return array_column(app(MateriasSolicitud::class)->todas(), 'id');
    }

    public function test_ofrece_solo_los_cursos_de_la_malla_del_estudiante(): void
    {
        $this->actingAs($this->estudiante($this->malla($this->deLaMalla->materia)));

        $this->assertSame([(string) $this->deLaMalla->id], $this->ofrecidos());
        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()
            ->assertSee($this->deLaMalla->materia->nombre)
            ->assertDontSee($this->fueraDeLaMalla->materia->nombre);
    }

    public function test_rechaza_un_curso_fuera_de_la_malla(): void
    {
        $this->actingAs($this->estudiante($this->malla($this->deLaMalla->materia)));

        $this->putJson(route('borradores-temporales.update', 'recuperacion'), [
            'paso' => 2, 'origen_materias' => 'catalogo',
            'materias' => [['id' => (string) $this->fueraDeLaMalla->id, 'datos' => []]],
        ])->assertUnprocessable()->assertJsonValidationErrors('materias.0.id');
        $this->putJson(route('borradores-temporales.update', 'recuperacion'), [
            'paso' => 2, 'origen_materias' => 'catalogo',
            'materias' => [['id' => (string) $this->deLaMalla->id, 'datos' => []]],
        ])->assertOk();
    }

    public function test_une_las_mallas_de_varias_inscripciones_activas(): void
    {
        $usuario = $this->estudiante($this->malla($this->deLaMalla->materia));
        CarreraEstudiante::factory()->create([
            'estudiante_id' => $usuario->estudiante->id,
            'malla_id' => $this->malla($this->fueraDeLaMalla->materia)->id,
        ]);
        $this->actingAs($usuario);

        $this->assertEqualsCanonicalizing(
            [(string) $this->deLaMalla->id, (string) $this->fueraDeLaMalla->id], $this->ofrecidos());
    }

    public function test_ignora_la_malla_de_una_inscripcion_inactiva(): void
    {
        $usuario = $this->estudiante($this->malla($this->deLaMalla->materia));
        CarreraEstudiante::factory()->create([
            'estudiante_id' => $usuario->estudiante->id,
            'malla_id' => $this->malla($this->fueraDeLaMalla->materia)->id, 'estado' => 'retirado',
        ]);
        $this->actingAs($usuario);

        $this->assertSame([(string) $this->deLaMalla->id], $this->ofrecidos());
    }

    public function test_sin_malla_conocida_ofrece_todo_el_catalogo(): void
    {
        $this->actingAs($this->estudiante(null));

        $this->assertSame(
            [(string) $this->deLaMalla->id, (string) $this->fueraDeLaMalla->id], $this->ofrecidos());
    }
}

<?php
namespace App\Services\Academico;

use App\Models\Curso;
use App\Models\Malla;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Periodo;
use Illuminate\Support\Carbon;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Trabajador;
use Illuminate\Support\Facades\DB;

class CatalogoAcademicoService
{
    // C01
    public function facultades(bool $soloActivas=true): Collection
    {
        if ($soloActivas){
            return Facultad::where('activo', true)->get();
        }

        return Facultad::all();
    }

    // C02
    public function carrerasPorFacultad(?int $facultadId = null, bool $soloActivas = true): Collection
    {
        return Carrera::query()
            ->when($facultadId, fn ($q) => $q->where('facultad_id', $facultadId))
            ->when($soloActivas, fn ($q) => $q->where('activo', true))
            ->orderBy('nombre')
            ->get();
    }

    // C03
    public function modalidadesDeCarrera(int $carreraId): Collection
    {
        return Modalidad::whereIn('id', DB::table('carrera_modalidad')->where('carrera_id', $carreraId)->select('modalidad_id'))
            ->orderBy('nombre')
            ->get();
    }

    // C06
    public function periodos(bool $soloActivos = false): Collection
    {
        return Periodo::query()
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->orderByDesc('fecha_inicio')
            ->get();
    }

    // C07
    public function periodoActual()
    {
        return \App\Models\Periodo::where('activo', true)->first();
    }

    // C09
    public function cursosDeMateria(int $materiaId, int $periodoId): Collection
    {
        return Curso::with('profesor')
                    ->where('materia_id', $materiaId)
                    ->where('periodo_id', $periodoId)
                    ->get();
    }

    // C04
    public function mallasDeCarrera(int $carreraId): Collection
    {
        return Malla::where('carrera_id', $carreraId)
                    ->orderByDesc('vigente')
                    ->orderByDesc('version')
                    ->get();
    }

    // C05
    public function materiasDeMalla(int $mallaId, ?string $buscar = null): Collection
    {
        $buscar = $buscar !== null ? mb_strtolower(trim($buscar)) : '';

        return Materia::query()
            ->select('materias.*', 'malla_materia.nivel')
            ->join('malla_materia', 'malla_materia.materia_id', '=', 'materias.id')
            ->where('malla_materia.malla_id', $mallaId)
            ->where('materias.activo', true)
            ->when($buscar !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->whereRaw('LOWER(materias.codigo) LIKE ?', ['%'.$buscar.'%'])
                ->orWhereRaw('LOWER(materias.nombre) LIKE ?', ['%'.$buscar.'%'])))
            ->orderBy('malla_materia.nivel')
            ->orderBy('materias.nombre')
            ->get();
    }

    // C08
    public function semanaActual(?Carbon $fecha = null) : ?int
    {
        $fecha = $fecha ?? now();
        $periodo = Periodo::where('activo', true)
                    ->first();
        if (! $periodo) {
            return null;
        }
        $inicio = Carbon::parse($periodo->fecha_inicio);
        $fin = Carbon::parse($periodo->fecha_fin);
        if ($fecha->lt($inicio) || $fecha->gt($fin->endOfDay())) {
            return null;
        }
        $dias = (int) $inicio->diffInDays($fecha);
        return intdiv($dias, 7) + 1;
    }

    // C10
    public function docentes(bool $soloActivos = true): Collection
    {
        return Trabajador::conRol('docente')
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    // C31
    public function carrerasSinTercerRegistro(): Collection
    {
        return Carrera::where('admite_tercer_registro', false)->get();
    }
}


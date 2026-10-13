<?php

namespace App\Models;

use Database\Factories\SolicitudFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Expediente de un trámite. Los datos académicos los declara el estudiante
 * y la asistente los verifica.
 *
 * Las relaciones hacia modelos de G1, G3 y G4 siguen la hoja «Modelos» de
 * modelos.xlsx; esas clases todavía no están en develop, así que solo se
 * pueden usar cuando cada grupo publique las suyas.
 */
class Solicitud extends Model
{
    /** @use HasFactory<SolicitudFactory> */
    use HasFactory;

    protected $table = 'solicitudes';

    /**
     * gpa_verificado y codigo_sistema quedan fuera: solo los escribe SolicitudService.
     * estado_solicitud_id solo lo cambia EstadoSolicitudService (G3).
     *
     * @var list<string>
     */
    protected $fillable = [
        'codigo',
        'carrera_estudiante_id',
        'tipo_tramite_id',
        'periodo_id',
        'estado_solicitud_id',
        'detalle',
        'gpa_declarado',
        'fecha_ultima_recuperacion',
        'declaracion_veracidad',
        'enviada_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'detalle' => 'array',
            'gpa_declarado' => 'decimal:2',
            'gpa_verificado' => 'boolean',
            'fecha_ultima_recuperacion' => 'date',
            'declaracion_veracidad' => 'boolean',
            'enviada_at' => 'datetime',
        ];
    }

    // Scopes publicados (contrato C42). Toda consulta de solicitudes se arma
    // encadenándolos. Los que recorren modelos de G1 esperan estas relaciones:
    // CarreraEstudiante::estudiante(), CarreraEstudiante::carreraModalidad()
    // y CarreraModalidad::carrera().

    public function scopeDeFacultad(Builder $query, int $facultadId): Builder
    {
        return $query->whereHas(
            'carreraEstudiante.carreraModalidad.carrera',
            fn (Builder $carrera) => $carrera->where('facultad_id', $facultadId)
        );
    }

    public function scopeDeCarrera(Builder $query, int $carreraId): Builder
    {
        return $query->whereHas(
            'carreraEstudiante.carreraModalidad',
            fn (Builder $carreraModalidad) => $carreraModalidad->where('carrera_id', $carreraId)
        );
    }

    /**
     * @param  string|list<string>  $codigosEstado  Constantes de App\Support\Estados (G3).
     */
    public function scopeEnEstado(Builder $query, string|array $codigosEstado): Builder
    {
        return $query->whereHas(
            'estadoSolicitud',
            fn (Builder $estado) => $estado->whereIn('codigo', (array) $codigosEstado)
        );
    }

    public function scopeDelPeriodo(Builder $query, int $periodoId): Builder
    {
        return $query->where($query->qualifyColumn('periodo_id'), $periodoId);
    }

    public function scopeDelTramite(Builder $query, int $tipoTramiteId): Builder
    {
        return $query->where($query->qualifyColumn('tipo_tramite_id'), $tipoTramiteId);
    }

    public function scopeDelEstudiante(Builder $query, int $estudianteId): Builder
    {
        return $query->whereHas(
            'carreraEstudiante',
            fn (Builder $carreraEstudiante) => $carreraEstudiante->where('estudiante_id', $estudianteId)
        );
    }

    /**
     * Busca por código de solicitud, código CAFI o datos del estudiante.
     * Cada palabra del texto debe aparecer en alguno de esos campos.
     */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        $palabras = preg_split('/\s+/', trim((string) $texto), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($palabras as $palabra) {
            $patron = '%'.$palabra.'%';

            $query->where(fn (Builder $coincide) => $coincide
                ->whereLike($query->qualifyColumn('codigo'), $patron)
                ->orWhereLike($query->qualifyColumn('codigo_sistema'), $patron)
                ->orWhereHas('carreraEstudiante.estudiante', fn (Builder $estudiante) => $estudiante
                    ->where(fn (Builder $datos) => $datos
                        ->whereLike('nombres', $patron)
                        ->orWhereLike('apellidos', $patron)
                        ->orWhereLike('codigo', $patron)
                        ->orWhereLike('cedula', $patron))));
        }

        return $query;
    }

    // Modelos de otros grupos

    public function carreraEstudiante(): BelongsTo
    {
        return $this->belongsTo(CarreraEstudiante::class);
    }

    public function tipoTramite(): BelongsTo
    {
        return $this->belongsTo(TipoTramite::class);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function estadoSolicitud(): BelongsTo
    {
        return $this->belongsTo(EstadoSolicitud::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialSolicitud::class);
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(Revision::class);
    }

    public function resolucion(): HasOne
    {
        return $this->hasOne(Resolucion::class);
    }

    public function cortesAgenda(): BelongsToMany
    {
        return $this->belongsToMany(CorteAgenda::class, 'corte_solicitud')->using(CorteSolicitud::class);
    }

    public function evaluacionesElegibilidad(): HasMany
    {
        return $this->hasMany(EvaluacionElegibilidad::class);
    }

    // Modelos de G2

    public function materias(): HasMany
    {
        return $this->hasMany(SolicitudMateria::class);
    }

    public function respaldos(): HasMany
    {
        return $this->hasMany(Respaldo::class);
    }

    public function documentosGenerados(): HasMany
    {
        return $this->hasMany(DocumentoGenerado::class);
    }
}

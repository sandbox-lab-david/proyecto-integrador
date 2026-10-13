<?php

namespace App\Models;

use App\Enums\EstadoMateria;
use Database\Factories\SolicitudMateriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Materia de una solicitud (0 a 10) con los datos académicos declarados
 * por el estudiante y su verificación.
 */
class SolicitudMateria extends Model
{
    /** @use HasFactory<SolicitudMateriaFactory> */
    use HasFactory;

    protected $table = 'solicitud_materia';

    /**
     * Las columnas de verificación quedan fuera: solo las escribe
     * SolicitudService::verificarMateria().
     *
     * @var list<string>
     */
    protected $fillable = [
        'solicitud_id',
        'curso_id',
        'materia_externa',
        'institucion_externa',
        'nota_declarada',
        'asistencia_declarada',
        'estado_declarado',
        'intento_declarado',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nota_declarada' => 'decimal:2',
            'asistencia_declarada' => 'decimal:2',
            'estado_declarado' => EstadoMateria::class,
            'intento_declarado' => 'integer',
            'verificado' => 'boolean',
            'verificado_at' => 'datetime',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /** Curso de G1; nulo cuando la materia es de otra institución. */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** Trabajador (G1) que verificó los datos declarados. */
    public function verificador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class, 'verificado_por');
    }

    public function registrosAnteriores(): HasMany
    {
        return $this->hasMany(RegistroAnterior::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\RegistroAnteriorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro previo reprobado de una materia; el tercer registro pide dos.
 */
class RegistroAnterior extends Model
{
    /** @use HasFactory<RegistroAnteriorFactory> */
    use HasFactory;

    protected $table = 'registros_anteriores';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'solicitud_materia_id',
        'anio',
        'periodo_texto',
        'nota',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'nota' => 'decimal:2',
        ];
    }

    public function solicitudMateria(): BelongsTo
    {
        return $this->belongsTo(SolicitudMateria::class);
    }
}

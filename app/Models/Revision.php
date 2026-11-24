<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    use HasFactory;

    protected $table = 'revisiones'; // Si tu tabla se llama revisiones

    protected $fillable = [
        'solicitud_id',
        'trabajador_id',
        'documento_legible',
        'datos_coinciden',
        'requisitos_completos',
        'documentos_seleccionados',
        'observaciones_internas',
        'decision'
    ];

    protected function casts(): array
    {
        return ['documentos_seleccionados' => 'array'];
    }
}

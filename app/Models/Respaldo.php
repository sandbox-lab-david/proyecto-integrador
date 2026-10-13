<?php

namespace App\Models;

use Database\Factories\RespaldoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Archivo de respaldo subido por el estudiante. «ruta» apunta al storage
 * privado, no es una URL pública.
 */
class Respaldo extends Model
{
    /** @use HasFactory<RespaldoFactory> */
    use HasFactory;

    protected $table = 'respaldos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'solicitud_id',
        'documento_requerido_id',
        'nombre_original',
        'ruta',
        'mime',
        'tamano_bytes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tamano_bytes' => 'integer',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /** Requisito del catálogo (G4) al que corresponde el archivo. */
    public function documentoRequerido(): BelongsTo
    {
        return $this->belongsTo(DocumentoRequerido::class);
    }
}

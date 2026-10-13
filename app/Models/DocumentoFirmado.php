<?php

namespace App\Models;

use Database\Factories\DocumentoFirmadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PDF firmado y escaneado que el estudiante vuelve a subir: uno por versión
 * del documento generado.
 */
class DocumentoFirmado extends Model
{
    /** @use HasFactory<DocumentoFirmadoFactory> */
    use HasFactory;

    protected $table = 'documentos_firmados';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'documento_generado_id',
        'ruta',
        'tamano_bytes',
        'subido_por',
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

    public function documentoGenerado(): BelongsTo
    {
        return $this->belongsTo(DocumentoGenerado::class);
    }

    /** Usuario que subió el PDF. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}

<?php

namespace App\Models;

use Database\Factories\DocumentoGeneradoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Documento oficial generado desde la plantilla: una versión por cada corrección.
 */
class DocumentoGenerado extends Model
{
    /** @use HasFactory<DocumentoGeneradoFactory> */
    use HasFactory;

    protected $table = 'documentos_generados';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'solicitud_id',
        'version',
        'ruta_docx',
        'ruta_pdf',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function documentoFirmado(): HasOne
    {
        return $this->hasOne(DocumentoFirmado::class);
    }
}

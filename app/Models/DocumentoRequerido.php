<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoRequerido extends Model
{
    protected $table = 'documentos_requeridos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['contenido' => 'array'];
    }

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class);
    }
}

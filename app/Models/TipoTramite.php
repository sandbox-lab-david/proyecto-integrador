<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTramite extends Model
{
    protected $table = 'tipos_tramite';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['contenido' => 'array'];
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoRequerido::class);
    }
}

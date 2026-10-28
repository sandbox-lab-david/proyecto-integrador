<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitoTramite extends Model
{
    protected $table = 'requisitos_tramite';

    protected $guarded = ['id'];

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class);
    }
}

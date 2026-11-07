<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PalabraClave extends Model
{
    protected $table = 'palabras_clave';

    protected $guarded = ['id'];

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class);
    }
}

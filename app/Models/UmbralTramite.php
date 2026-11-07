<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmbralTramite extends Model
{
    protected $table = 'umbrales_tramite';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['valor' => 'float'];
    }

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class);
    }
}

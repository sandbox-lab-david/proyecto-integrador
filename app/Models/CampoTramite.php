<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampoTramite extends Model
{
    protected $table = 'campos_tramite';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['obligatorio' => 'boolean', 'configuracion' => 'array'];
    }

    public function definicion(): array
    {
        return array_replace($this->configuracion, ['nombre' => $this->nombre, 'etiqueta' => $this->etiqueta,
            'obligatorio' => $this->obligatorio, 'tipo' => $this->tipo]);
    }

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class);
    }
}

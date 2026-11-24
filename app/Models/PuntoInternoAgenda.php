<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntoInternoAgenda extends Model
{
    protected $table = 'puntos_internos_agenda';

    protected $fillable = ['corte_agenda_id', 'titulo', 'descripcion'];

    public function corteAgenda()
    {
        return $this->belongsTo(CorteAgenda::class, 'corte_agenda_id');
    }
}

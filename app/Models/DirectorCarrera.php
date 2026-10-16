<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectorCarrera extends Model
{
    protected $fillable = [
        'carrera_id',
        'trabajador_id',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectorCarrera extends Model
{
    use HasFactory;

    protected $table = 'directores_carrera';

    protected $fillable = [
        'carrera_id',
        'trabajador_id',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
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

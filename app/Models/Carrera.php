<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';

    protected $fillable = [
        'facultad_id', 'codigo', 'nombre', 'nivel', 'admite_tercer_registro', 'activo',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }
    public function modalidades()
    {
        return $this->belongsToMany(Modalidad::class, 'carrera_modalidad');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $fillable = [
        'materia_id',
        'periodo_id',
        'profesor_id',
        'paralelo',
        'cupo',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function soliciudMaterias()
    {
        return $this->hasMany(SolicitudMateria::class);
    }
}

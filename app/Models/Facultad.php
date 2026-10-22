<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    use HasFactory;

    protected $table = 'facultades';

    protected $fillable = [
        'codigo', 'nombre', 'correo_notificaciones', 'activo',
    ];

    public function carreras()
    {
        return $this->hasMany(Carrera::class);
    }

    public function trabajadores()
    {
        return $this->belongsToMany(Trabajador::class, 'facultad_trabajador')->withTimestamps();
    }
}

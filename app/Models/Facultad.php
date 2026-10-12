<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultades';

    protected $fillable = [
        'codigo', 'nombre', 'correo_notificaciones', 'activo',
    ];

    public function carreras()
    {
        return $this->hasMany(Carrera::class);
    }
}
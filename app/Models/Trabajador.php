<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $table = 'trabajadores';

    protected $fillable = [
        'user_id', 'codigo', 'nombres', 'apellidos', 'correo', 'activo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_trabajador');
    }

    public function directoresCarrera()
    {
        return $this->hasMany(DirectorCarrera::class);
    }
}

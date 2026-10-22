<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'codigo', 'nombre', 'descripcion',
    ];

    public function trabajadores()
    {
        return $this->belongsToMany(Trabajador::class, 'rol_trabajador');
    }
}

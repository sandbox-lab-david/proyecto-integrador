<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolTrabajador extends Model
{
    use HasFactory;

    protected $table = 'rol_trabajador';
    protected $fillable = ['trabajador_id', 'rol_codigo', 'activo'];
}
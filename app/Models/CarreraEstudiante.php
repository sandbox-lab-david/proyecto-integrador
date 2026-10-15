<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarreraEstudiante extends Model
{
    use HasFactory;

    protected $table = 'carrera_estudiante';
    protected $fillable = ['estudiante_id', 'carrera_id', 'estado_inscripcion', 'periodo_ingreso'];
}
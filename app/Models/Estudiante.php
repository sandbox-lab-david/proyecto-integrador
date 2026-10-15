<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;
    protected $table = 'estudiantes';

    protected $fillable = ['user_id','codigo','cedula','nombres','apellidos','correo','celular'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function carrerasEstudiante()
    {
        return $this->hasMany(CarreraEstudiante::class);
    }
}

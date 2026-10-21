<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarreraEstudiante extends Model
{
    use HasFactory;

    protected $table = 'carrera_estudiante';

    protected $fillable = ['user_id', 'carrera_id', 'periodo_id', 'estado'];

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'user_id', 'user_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

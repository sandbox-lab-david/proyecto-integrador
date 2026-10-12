<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarreraModalidad extends Model
{
    /** @use HasFactory<\Database\Factories\CarreraModalidadFactory> */
    use HasFactory;

    protected $table = 'carrera_modalidad';
    protected $fillable = ['carrera_id', 'modalidad_id'];
    
        public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }
        public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }
        public function carreraEstudiantes()
    {
        return $this->hasMany(CarreraEstudiante::class);
    }
}

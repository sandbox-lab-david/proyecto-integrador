<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarreraEstudiante extends Model
{
    use HasFactory;

    public const ESTADOS = ['activo', 'inactivo', 'egresado', 'retirado'];

    protected $table = 'carrera_estudiante';

    protected $fillable = ['estudiante_id', 'carrera_modalidad_id', 'malla_id', 'fecha_inscripcion', 'fecha_fin', 'estado'];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function carreraModalidad()
    {
        return $this->belongsTo(CarreraModalidad::class);
    }

    public function malla()
    {
        return $this->belongsTo(Malla::class);
    }

    public function carrera()
    {
        return $this->hasOneThrough(Carrera::class, CarreraModalidad::class, 'id', 'id', 'carrera_modalidad_id', 'carrera_id');
    }

    protected function userId(): Attribute
    {
        return Attribute::get(fn () => $this->attributes['user_id'] ?? $this->estudiante?->user_id);
    }

    protected function carreraId(): Attribute
    {
        return Attribute::get(fn () => $this->attributes['carrera_id'] ?? $this->carreraModalidad?->carrera_id);
    }
}

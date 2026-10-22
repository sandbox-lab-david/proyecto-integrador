<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Malla extends Model
{
    use HasFactory;

    protected $table = 'mallas';

    protected $fillable = [
        'carrera_id', 'version', 'vigente',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'malla_materia')->withPivot('nivel')->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';
    protected $fillable = ['codigo', 'nombre', 'creditos', 'semestre', 'malla_id', 'categoria_materia_id', 'activo'];

    public function categoria()
    {
        return $this->belongsTo(CategoriaMateria::class, 'categoria_materia_id');
    }
}
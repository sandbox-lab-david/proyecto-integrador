<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaMateria extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaMateriaFactory> */
    use HasFactory;

    protected $table = 'categorias_materia';

    protected $fillable = ['nombre'];

    public function materias()
    {
        return $this->hasMany(Materia::class);
    }
}

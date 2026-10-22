<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MallaMateria extends Model
{
    protected $table = 'malla_materia';

    protected $fillable = ['malla_id', 'materia_id', 'nivel'];

    public function malla()
    {
        return $this->belongsTo(Malla::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }
}

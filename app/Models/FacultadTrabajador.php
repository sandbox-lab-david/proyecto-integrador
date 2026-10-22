<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultadTrabajador extends Model
{
    use HasFactory;

    protected $table = 'facultad_trabajador';

    protected $fillable = ['trabajador_id', 'facultad_id'];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }
}

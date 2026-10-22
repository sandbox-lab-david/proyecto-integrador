<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'periodos';

    protected $fillable = [
        'codigo', 'nombre', 'tipo', 'fecha_inicio', 'fecha_fin', 'semana_primer_parcial', 'activo',
    ];

}

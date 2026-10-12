<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    protected $fillable = [
        'entidad',
        'archivo_ruta',
        'useri_id',
        'estado',
        'total_filas',
        'creadas',
        'actualizadas',
        'rechazadas',
        'errores',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

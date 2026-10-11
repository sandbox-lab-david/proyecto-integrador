<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoSolicitud extends Model
{
    protected $table = 'estados_solicitud';
    protected $fillable = ['codigo', 'nombre', 'descripcion'];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'estado_id');
    }
}
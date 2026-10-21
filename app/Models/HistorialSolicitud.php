<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialSolicitud extends Model
{
    protected $table = 'historial_solicitud';

    protected $fillable = ['solicitud_id', 'estado_anterior', 'estado_nuevo', 'user_id', 'comentario', 'visible_estudiante'];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

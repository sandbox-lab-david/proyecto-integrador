<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteAgenda extends Model
{
    protected $table = 'cortes_agenda';

    protected $fillable = ['facultad_id', 'fecha_sesion', 'asistentes', 'estado'];

    protected function casts(): array
    {
        return ['fecha_sesion' => 'date'];
    }

    public function solicitudes()
    {
        return $this->belongsToMany(Solicitud::class, 'corte_solicitud', 'corte_agenda_id', 'solicitud_id')->withTimestamps();
    }

    public function puntosInternos()
    {
        return $this->hasMany(PuntoInternoAgenda::class, 'corte_agenda_id');
    }

    public function resoluciones()
    {
        return $this->hasMany(Resolucion::class, 'corte_agenda_id');
    }
}

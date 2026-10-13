<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    protected $table = 'resoluciones';
    protected $fillable = [
        'solicitud_id', 
        'corte_agenda_id', 
        'numero_resolucion', 
        'fecha_resolucion', 
        'decision', 
        'observacion', 
        'documento_pdf', 
        'registrada_por'
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function corteAgenda()
    {
        return $this->belongsTo(CorteAgenda::class, 'corte_agenda_id');
    }
}
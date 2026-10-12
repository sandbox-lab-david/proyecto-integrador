<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Malla extends Model
{
    protected $table = 'mallas';

    protected $fillable = [
        'carrera_id', 'version', 'vigente',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

}

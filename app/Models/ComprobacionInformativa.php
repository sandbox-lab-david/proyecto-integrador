<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobacionInformativa extends Model
{
    protected $table = 'comprobaciones_informativas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['respuesta' => 'array'];
    }
}

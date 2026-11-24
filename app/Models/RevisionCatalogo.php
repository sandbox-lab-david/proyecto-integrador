<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionCatalogo extends Model
{
    protected $table = 'revisiones_catalogo';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['contenido' => 'array', 'activa' => 'boolean'];
    }
}

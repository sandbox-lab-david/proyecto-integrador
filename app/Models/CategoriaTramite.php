<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaTramite extends Model
{
    protected $table = 'categorias_tramite';

    protected $guarded = ['id'];

    public function tramites()
    {
        return $this->hasMany(TipoTramite::class, 'categoria_tramite_id');
    }
}

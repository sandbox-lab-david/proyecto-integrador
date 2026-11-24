<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTramite extends Model
{
    protected $table = 'tipos_tramite';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['contenido' => 'array', 'catalogo_normalizado' => 'boolean'];
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoRequerido::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaTramite::class, 'categoria_tramite_id');
    }

    public function requisitos()
    {
        return $this->hasMany(RequisitoTramite::class)->orderBy('orden')->orderBy('id');
    }

    public function campos()
    {
        return $this->hasMany(CampoTramite::class)->orderBy('orden')->orderBy('id');
    }

    public function umbrales()
    {
        return $this->hasMany(UmbralTramite::class)->orderBy('orden')->orderBy('id');
    }

    public function facultades()
    {
        return $this->belongsToMany(Facultad::class, 'facultad_tipo_tramite', 'tipo_tramite_id', 'facultad_id')->withTimestamps();
    }

    public function palabrasClave()
    {
        return $this->hasMany(PalabraClave::class)->orderBy('id');
    }
}

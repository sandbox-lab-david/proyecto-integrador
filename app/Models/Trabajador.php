<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trabajador extends Model
{
    use HasFactory;

    protected $table = 'trabajadores';

    protected $fillable = [
        'user_id', 'codigo', 'nombres', 'apellidos', 'correo', 'activo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_trabajador');
    }

    public function directoresCarrera()
    {
        return $this->hasMany(DirectorCarrera::class);
    }

    public function facultades()
    {
        return $this->belongsToMany(Facultad::class, 'facultad_trabajador')->withTimestamps();
    }

    public function scopeConRol(Builder $consulta, string|array $codigos): Builder
    {
        $codigos = (array) $codigos;

        return $consulta->whereIn('trabajadores.id', DB::table('rol_trabajador')
            ->leftJoin('roles', 'roles.id', '=', 'rol_trabajador.rol_id')
            ->where(fn ($q) => $q->whereIn('rol_trabajador.rol', $codigos)->orWhereIn('roles.codigo', $codigos))
            ->select('rol_trabajador.trabajador_id'));
    }
}

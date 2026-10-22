<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    use HasFactory;

    public const ESTADOS = ['pendiente', 'procesando', 'completada', 'fallida'];

    protected $table = 'importaciones';

    protected $fillable = [
        'entidad',
        'archivo_ruta',
        'user_id',
        'estado',
        'total_filas',
        'creadas',
        'actualizadas',
        'rechazadas',
        'errores',
    ];

    protected $casts = [
        'errores' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nombreArchivo(): string
    {
        return basename($this->archivo_ruta);
    }
}

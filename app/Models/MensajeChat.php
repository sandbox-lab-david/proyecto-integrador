<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeChat extends Model
{
    use HasFactory;

    protected $table = 'mensajes_chat';

    protected $fillable = [
        'conversacion_id',
        'emisor',
        'texto',
        'intencion',
        'tramite_id',
        'tipo_tramite_id',
        'resuelta',
        'tiempo_ms',
        'payload',
    ];

    protected $casts = [
        'resuelta' => 'boolean',
        'tiempo_ms' => 'integer',
        'payload' => 'array',
    ];

    /**
     * Conversación a la que pertenece el mensaje.
     */
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class, 'conversacion_id');
    }
}

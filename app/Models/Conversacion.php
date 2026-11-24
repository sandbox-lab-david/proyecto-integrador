<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Usuario propietario de la conversación (null si es anónimo).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mensajes que componen esta conversación.
     */
    public function mensajes(): HasMany
    {
        return $this->hasMany(MensajeChat::class, 'conversacion_id');
    }
}

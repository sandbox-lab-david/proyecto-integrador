<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;
use App\Services\Academico\AutorizacionService;

/**
 * El único lugar que decide quién puede ver o tocar una solicitud (contrato C60).
 *
 * La propiedad se lee por carrera_estudiante → estudiantes.user_id. Solo el estudiante propietario
 * activo puede corregir una solicitud observada; el personal conserva los
 * permisos de lectura publicados por G1, salvo sobre los borradores.
 */
class SolicitudPolicy
{
    public function update(User $u, Solicitud $s): bool
    {
        return $u->activo && $u->tipo === 'estudiante'
            && (int) $s->carreraEstudiante?->estudiante?->user_id === (int) $u->getKey()
            && in_array(strtolower((string) $s->estadoSolicitud?->codigo), ['observada', 'observado'], true);
    }

    public function subirFirmado(User $u, Solicitud $s): bool
    {
        return $this->update($u, $s);
    }

    public function __construct(private readonly AutorizacionService $autorizacion) {}

    /**
     * El estudiante ve las suyas; el personal, las de su carrera o su facultad.
     */
    public function view(User $u, Solicitud $s): bool
    {
        if (! $u->activo || ! $s->carreraEstudiante) {
            return false;
        }
        if ((int) $s->carreraEstudiante->estudiante?->user_id === (int) $u->id) {
            return true;
        }
        // Un borrador (sin enviada_at) es solo de su estudiante hasta que lo envía.
        if ($s->enviada_at === null) {
            return false;
        }

        $carrera = $s->carreraEstudiante->carreraModalidad?->carrera;

        return $carrera !== null
            && ($this->autorizacion->puedeVerCarrera($u, $carrera->getKey())
                || $this->autorizacion->puedeVerFacultad($u, $carrera->facultad_id));
    }

    /**
     * Quien puede ver la solicitud puede descargar sus documentos.
     */
    public function descargar(User $u, Solicitud $s): bool
    {
        return $this->view($u, $s);
    }
}

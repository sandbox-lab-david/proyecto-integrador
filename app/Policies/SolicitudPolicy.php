<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;
use App\Services\Academico\AutorizacionService;

/**
 * El único lugar que decide quién puede ver o tocar una solicitud (contrato C60).
 *
 * Por ahora publica «view» y «descargar»; «update» y «subirFirmado» dependen
 * del estado del caso y llegan con la corrección (RF-2.14) y el envío (RF-2.11).
 *
 * Está escrita contra los contratos de G1 y todavía no se ha ejecutado. Supone
 * las relaciones CarreraEstudiante::estudiante() y ::carreraModalidad(),
 * CarreraModalidad::carrera() y las columnas estudiantes.user_id y
 * carreras.facultad_id, igual que los scopes de Solicitud.
 */
class SolicitudPolicy
{
    public function __construct(private readonly AutorizacionService $autorizacion) {}

    /**
     * El estudiante ve las suyas; el personal, las de su carrera o su facultad.
     */
    public function view(User $u, Solicitud $s): bool
    {
        $inscripcion = $s->carreraEstudiante;

        if ((int) $inscripcion->estudiante->user_id === (int) $u->getKey()) {
            return true;
        }

        $carrera = $inscripcion->carreraModalidad->carrera;

        return $this->autorizacion->puedeVerCarrera($u, $carrera->getKey())
            || $this->autorizacion->puedeVerFacultad($u, $carrera->facultad_id);
    }

    /**
     * Quien puede ver la solicitud puede descargar sus documentos.
     */
    public function descargar(User $u, Solicitud $s): bool
    {
        return $this->view($u, $s);
    }
}

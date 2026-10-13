<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\User;

/**
 * Punto de integración pendiente. No es un contrato ya publicado por G1/G3/G4.
 * El adaptador debe resolver identidad, permisos y catálogos reales en el servidor,
 * rechazar datos de demostración y comprobar elegibilidad antes de devolver IDs.
 */
interface IntegracionEnvio
{
    /**
     * @return array{carrera_estudiante_id: int, tipo_tramite_id: int, periodo_id: int,
     * estado_solicitud_id: int, cursos: array<string, int>, requisitos: array<string, int>}
     * estado_solicitud_id es el estado previo al envío; cursos/requisitos mapean códigos a IDs reales.
     */
    public function resolver(User $usuario, array $datos): array;

    /** G3 registra el cambio a Enviado y su historial dentro de la transacción. */
    public function enviar(Solicitud $solicitud, User $usuario): void;
}

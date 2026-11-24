<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\User;

/**
 * Punto de integración pendiente. No es un contrato ya publicado por G1/G3/G4.
 * El adaptador debe resolver identidad, permisos y destino de revisión en el servidor.
 * Los datos académicos y las materias se declaran en el formulario, sin importar SIAC.
 * No debe usar el nombre/correo declarado para elegir al propietario de la solicitud.
 */
interface IntegracionEnvio
{
    /**
     * @return array{carrera_estudiante_id: int, tipo_tramite_id: int, periodo_id: int,
     * estado_solicitud_id: int, cursos?: array<string, int>, requisitos: array<string, int>}
     * estado_solicitud_id es el estado previo al envío. La vinculación de cursos es opcional:
     * los datos manuales se conservan siempre en detalle.materias.
     */
    public function resolver(User $usuario, array $datos): array;

    /**
     * Contexto con el que se guarda un borrador (RF-2.12), que puede estar
     * incompleto: la inscripción activa y el periodo vigente se toman de la
     * cuenta, no de lo declarado. null si la cuenta no tiene con qué guardarlo
     * (sin inscripción activa, sin periodo vigente o sin el estado «borrador»).
     *
     * @return array{carrera_estudiante_id: int, tipo_tramite_id: int, periodo_id: int, estado_solicitud_id: int}|null
     */
    public function contextoBorrador(User $usuario, string $tramite, array $datos): ?array;

    /** G3 registra el cambio a Enviado y su historial dentro de la transacción. */
    public function enviar(Solicitud $solicitud, User $usuario): void;
}

<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\SolicitudMateria;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

/**
 * Lectura y escritura de solicitudes para los demás grupos (contratos C43–C47).
 *
 * El estado de la solicitud no se cambia aquí: eso lo hace
 * EstadoSolicitudService de G3.
 */
class SolicitudService
{
    /**
     * Solicitud con todo su expediente cargado, para no repetir consultas
     * en el detalle del caso ni en el chat.
     */
    public function detalle(int $solicitudId): Solicitud
    {
        return Solicitud::with([
            'materias.registrosAnteriores',
            'respaldos',
            'documentosGenerados.documentoFirmado',
            'historial',
            'evaluacionesElegibilidad',
            'resolucion',
        ])->findOrFail($solicitudId);
    }

    /**
     * Solicitudes de un estudiante, de la más reciente a la más antigua.
     *
     * @return Collection<int, Solicitud>
     */
    public function delEstudiante(int $estudianteId): Collection
    {
        return Solicitud::delEstudiante($estudianteId)
            ->with(['tipoTramite', 'estadoSolicitud'])
            ->latest('id')
            ->get();
    }

    /**
     * La asistente confirma (o no) los datos académicos declarados de una materia.
     */
    public function verificarMateria(SolicitudMateria $m, Trabajador $t, bool $ok, ?string $obs): void
    {
        if ($obs !== null && mb_strlen($obs) > 500) {
            throw new InvalidArgumentException('La observación de la verificación admite hasta 500 caracteres.');
        }

        $m->forceFill([
            'verificado' => $ok,
            'verificado_por' => $t->getKey(),
            'verificado_at' => now(),
            'observacion_verificacion' => $obs,
        ])->save();
    }

    /**
     * La asistente confirma (o no) el GPA declarado. La tabla no guarda quién
     * lo verificó: eso queda en la revisión que registra G3.
     */
    public function verificarGpa(Solicitud $s, Trabajador $t, bool $ok): void
    {
        $s->forceFill(['gpa_verificado' => $ok])->save();
    }

    /**
     * Guarda el número CAFI que se imprime en la agenda del Consejo.
     */
    public function registrarCodigoSistema(Solicitud $s, string $codigo): void
    {
        $codigo = trim($codigo);

        if ($codigo === '' || mb_strlen($codigo) > 30) {
            throw new InvalidArgumentException('El código del sistema debe tener entre 1 y 30 caracteres.');
        }

        $s->forceFill(['codigo_sistema' => $codigo])->save();
    }

    /**
     * Siguiente identificador legible del año, con el formato SOL-AAAA-00000.
     *
     * Dos solicitudes creadas a la vez pueden recibir el mismo código: el
     * índice único de solicitudes.codigo rechaza la segunda, así que quien
     * crea la solicitud debe pedir otro código y reintentar.
     */
    public function siguienteCodigo(?int $anio = null): string
    {
        $prefijo = sprintf('SOL-%d-', $anio ?? now()->year);

        $ultimo = Solicitud::where('codigo', 'like', $prefijo.'%')
            ->orderByRaw('length(codigo) desc')
            ->orderByDesc('codigo')
            ->value('codigo');

        $numero = $ultimo === null ? 1 : (int) substr($ultimo, strlen($prefijo)) + 1;

        return $prefijo.str_pad((string) $numero, 5, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Borrador del formulario (RF-2.12). Una solicitud nueva se guarda como fila
 * de «solicitudes» en estado «borrador» y sin enviada_at, una por trámite y
 * estudiante, y se retoma desde cualquier navegador. Al enviarla,
 * EnvioSolicitud completa esa misma fila.
 *
 * Quedan en la sesión (BorradoresTemporales) los borradores de una corrección,
 * que no deben tocar la solicitud observada hasta reenviarla, y los de una
 * cuenta sin inscripción activa o sin periodo vigente, que todavía no tiene
 * con qué guardarlos en la base.
 */
class BorradoresSolicitud
{
    public function __construct(
        private readonly BorradoresTemporales $temporales,
        private readonly SolicitudService $solicitudes,
    ) {}

    /**
     * @param  array<string, mixed>  $datos  lo que el formulario permite guardar del trámite
     * @return array<string, mixed> lo guardado, con su «guardado_at» y si quedó «en_cuenta»
     */
    public function guardar(string $tramite, array $datos): array
    {
        $usuario = $this->usuario();
        $contexto = $usuario === null || ! app()->bound(IntegracionEnvio::class)
            ? null
            : app(IntegracionEnvio::class)->contextoBorrador($usuario, $tramite, $datos);

        // Un borrador que ya está en la cuenta sigue ahí aunque hoy falte el contexto.
        if ($contexto === null && ($usuario === null || ! $this->consulta($usuario, $tramite)->exists())) {
            return $this->temporales->guardar($tramite, $datos) + ['en_cuenta' => false];
        }

        $gpa = $datos['campos']['gpa_general'] ?? null;
        $fecha = $datos['campos']['ultima_recuperacion'] ?? null;
        $atributos = [
            'detalle' => $datos + ['tramite' => $tramite],
            'gpa_declarado' => is_numeric($gpa) ? $gpa : null,
            'fecha_ultima_recuperacion' => is_string($fecha) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : null,
        ];
        if ($contexto !== null) {
            $atributos += array_intersect_key($contexto, array_flip(['carrera_estudiante_id', 'tipo_tramite_id', 'periodo_id']));
        }

        for ($intento = 0; ; $intento++) {
            try {
                $borrador = DB::transaction(function () use ($usuario, $tramite, $atributos, $contexto) {
                    // Dos guardados del mismo estudiante a la vez no deben crear dos borradores.
                    User::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
                    $borrador = $this->consulta($usuario, $tramite)->lockForUpdate()->first();

                    if ($borrador === null) {
                        if ($contexto === null) {
                            return null; // Se descartó mientras se guardaba.
                        }

                        // El estado inicial se asigna al crear la fila; después solo lo cambia G3.
                        return Solicitud::create($atributos + [
                            'codigo' => $this->solicitudes->siguienteCodigo(),
                            'estado_solicitud_id' => $contexto['estado_solicitud_id'],
                            'declaracion_veracidad' => false,
                        ]);
                    }

                    $borrador->forceFill($atributos);
                    $borrador->isDirty() ? $borrador->save() : $borrador->touch();

                    return $borrador;
                }, 5);
                break;
            } catch (QueryException $error) {
                // Solo reintenta colisiones del código SOL; otros errores se propagan.
                if ($intento >= 4 || ! in_array($error->getCode(), ['23000', '23505'], true)
                    || ! str_contains($error->getMessage(), 'codigo')) {
                    throw $error;
                }
            }
        }

        if ($borrador === null) {
            return $this->temporales->guardar($tramite, $datos) + ['en_cuenta' => false];
        }

        // Lo que la sesión tenía de este trámite ya quedó en la cuenta.
        $this->temporales->descartar($tramite);

        return $this->comoArreglo($borrador);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscar(string $tramite): ?array
    {
        if (($usuario = $this->usuario()) !== null) {
            $borrador = $this->consulta($usuario, $tramite)->first();

            if ($borrador !== null) {
                return $this->comoArreglo($borrador);
            }
        }

        return $this->temporales->buscar($tramite);
    }

    /**
     * Los borradores guardados en la cuenta, del más reciente al más antiguo.
     *
     * @return Collection<int, Solicitud>
     */
    public function enCuenta(?User $usuario): Collection
    {
        if (! $usuario instanceof User || ! $usuario->exists) {
            return new Collection;
        }

        return $this->propios($usuario)->latest('updated_at')->latest('id')->get();
    }

    public function descartar(string $tramite): void
    {
        if (($usuario = $this->usuario()) !== null) {
            $this->consulta($usuario, $tramite)->get()->each->delete();
        }

        $this->temporales->descartar($tramite);
    }

    /**
     * El borrador en la cuenta de ese estudiante y trámite, si lo hay.
     */
    public function consulta(User $usuario, string $tramite): Builder
    {
        return $this->propios($usuario)->where('detalle->tramite', $tramite);
    }

    private function propios(User $usuario): Builder
    {
        return Solicitud::borradores()
            ->whereHas('carreraEstudiante.estudiante', fn ($q) => $q->where('user_id', $usuario->getKey()));
    }

    /**
     * La cuenta cuyos borradores se guardan en la base; null para una
     * corrección o sin una cuenta guardada.
     */
    private function usuario(): ?User
    {
        $usuario = auth()->user();

        return $usuario instanceof User && $usuario->exists && ! request()->filled('solicitud_correccion')
            ? $usuario : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function comoArreglo(Solicitud $borrador): array
    {
        $datos = $borrador->detalle ?? [];
        unset($datos['tramite']);

        return $datos + [
            'guardado_at' => $borrador->updated_at->toIso8601String(),
            'en_cuenta' => true,
        ];
    }
}

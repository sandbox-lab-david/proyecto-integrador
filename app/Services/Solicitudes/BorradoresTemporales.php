<?php

namespace App\Services\Solicitudes;

use Illuminate\Contracts\Session\Session;

/**
 * Borrador del formulario (RF-2.12) mientras la solicitud todavía no se puede
 * guardar. Vive en la sesión, uno por trámite, igual que los respaldos
 * temporales del paso 3. Cuando exista el guardado, el borrador será una
 * solicitud sin enviar y esta clase se podrá borrar.
 */
class BorradoresTemporales
{
    private const SESION = 'borradores_temporales';

    public function __construct(private readonly Session $sesion) {}

    /**
     * @param  array{paso: int, celular: ?string, materias: list<array{id: string, datos: array<string, ?string>}>, campos: array<string, ?string>, requisitos: array<string, ?string>}  $datos
     * @return array<string, mixed> lo guardado, con su «guardado_at»
     */
    public function guardar(string $tramite, array $datos): array
    {
        $borrador = $datos + ['guardado_at' => now()->toIso8601String()];

        $this->sesion->put(self::SESION.'.'.$tramite, $borrador);

        return $borrador;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscar(string $tramite): ?array
    {
        return $this->todos()[$tramite] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>> borradores de la sesión por código de trámite
     */
    public function todos(): array
    {
        return $this->sesion->get(self::SESION, []);
    }

    public function descartar(string $tramite): void
    {
        $this->sesion->forget(self::SESION.'.'.$tramite);
    }
}

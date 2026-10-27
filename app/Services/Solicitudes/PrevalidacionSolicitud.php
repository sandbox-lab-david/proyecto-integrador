<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Services\Catalogo\CatalogoTramites;
use App\Services\Chatbot\Prolog\PrologException;
use App\Services\Chatbot\PrologService;

/**
 * Pre-validación del paso 4 (RF-2.8). Consulta la fachada de G4 (C72) con los
 * datos que declaró el estudiante. El motor v3 no emite dictámenes: devuelve
 * comprobaciones informativas, que se muestran sin bloquear el envío.
 */
class PrevalidacionSolicitud
{
    /** Campo del formulario => dato del perfil que recibe Prolog. */
    private const PERFIL = [
        'gpa_general' => 'gpa_general',
        'ultima_recuperacion' => 'fecha_ultima_recuperacion',
    ];

    public function consultar(string $codigo, array $campos): array
    {
        $tramite = app(CatalogoTramites::class)->buscar($codigo);
        abort_unless(isset($tramite['formulario']), 404);
        try {
            $prolog = app(PrologService::class);
            $respuesta = $prolog->evaluar($tramite['id'], $this->perfil(
                $tramite['formulario'], $campos, array_column($prolog->camposFormulario($tramite['id']), 'nombre')));
        } catch (PrologException $error) {
            report($error);

            return ['disponible' => false,
                'error' => 'No se pudo consultar a Prolog en este momento. Vuelve a intentarlo en unos segundos.'];
        }

        return [
            'disponible' => true,
            'comprobaciones' => $respuesta['comprobaciones'],
            'items' => $respuesta['items'],
            'aviso' => $respuesta['aviso'],
        ];
    }

    /**
     * Solo viajan los datos que el motor admite para ese trámite y que el
     * formulario muestra: un campo condicional oculto no forma parte del perfil.
     */
    public function perfil(array $formulario, array $campos, array $admitidos): array
    {
        $perfil = [];
        foreach ($formulario['campos'] ?? [] as $campo) {
            $clave = self::PERFIL[$campo['nombre']] ?? null;
            $valor = $campos[$campo['nombre']] ?? null;
            $condicion = $campo['mostrar_si'] ?? null;
            if ($clave === null || ! in_array($clave, $admitidos, true) || ! is_string($valor) || trim($valor) === ''
                || ($condicion && ($campos[$condicion['campo']] ?? null) !== $condicion['valor'])) {
                continue;
            }
            $perfil[$clave] = trim($valor);
        }

        return $perfil;
    }

    /**
     * Deja junto al expediente enviado lo que informó el motor, para la revisión
     * (RF-4.10). Es orientativo: si Prolog falla, el envío ya está confirmado.
     */
    public function registrar(Solicitud $solicitud): void
    {
        try {
            app(PrologService::class)->evaluar($solicitud);
        } catch (\Throwable $error) {
            report($error);
        }
    }
}

<?php

namespace App\Services\Chatbot;

use App\Models\ComprobacionInformativa;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Catalogo\CatalogoTramites;
use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/** Fachada para otros módulos. C72 conserva su nombre, pero devuelve referencias sin dictámenes. */
class PrologService
{
    public function __construct(protected PrologClient $client, protected ChatbotCatalogoService $catalogoService) {}

    public function consultar(string $tramiteOTexto, string|User|null $intencionOUsuario = null): array
    {
        if (is_string($intencionOUsuario) && str_starts_with($intencionOUsuario, 'consultar_')) {
            return $this->client->consultar(['version' => 3, 'tramite_id' => $tramiteOTexto, 'intencion' => $intencionOUsuario]);
        }

        return app(ChatbotOrchestratorService::class)->procesarMensaje(['version' => 3, 'texto' => $tramiteOTexto]);
    }

    public function evaluar(mixed $solicitudOTramiteId, mixed $perfilOConVerificados = [], string $origenDatos = 'manual', bool $conDatosVerificados = false): array
    {
        $solicitud = $solicitudOTramiteId instanceof Solicitud ? $solicitudOTramiteId : null;
        if (is_object($solicitudOTramiteId)) {
            $obj = $solicitudOTramiteId;
            $codigo = $obj->tipoTramite?->codigo ?? data_get($obj->detalle, 'tramite') ?? ($obj->tramite ?? '');
            $tramiteId = app(CatalogoTramites::class)->buscar($codigo)['id'] ?? null;
            $perfil = ['gpa_general' => $obj->gpa_declarado ?? data_get($obj->detalle, 'campos.gpa_general'), 'fecha_ultima_recuperacion' => ($obj->fecha_ultima_recuperacion ?? null)?->format('Y-m-d') ?? data_get($obj->detalle, 'campos.ultima_recuperacion')];
        } else {
            $tramiteId = $solicitudOTramiteId;
            $perfil = is_array($perfilOConVerificados) ? $perfilOConVerificados : [];
        }
        if (! in_array($tramiteId, array_column($this->catalogoService->obtenerCatalogo()['tramites'], 'id'), true)) {
            throw new InvalidArgumentException('Trámite desconocido.');
        }
        Validator::make(['perfil' => $perfil], ['perfil' => 'array:gpa_general,fecha_ultima_recuperacion', 'perfil.gpa_general' => 'nullable|numeric|between:0,100', 'perfil.fecha_ultima_recuperacion' => 'nullable|date_format:Y-m-d|before_or_equal:'.now(ReglasCampoSolicitud::zonaHoraria())->toDateString()])->validate();
        if (isset($perfil['gpa_general'])) {
            $perfil['gpa_general'] = (float) $perfil['gpa_general'];
        }
        $r = $this->client->evaluar(['version' => 3, 'tramite_id' => $tramiteId, 'intencion' => 'comprobar_referencias', 'fecha' => now(ReglasCampoSolicitud::zonaHoraria())->toDateString(), 'origen_datos' => 'manual', 'perfil' => array_filter($perfil, fn ($v) => $v !== null && $v !== '')]);
        if ($solicitud?->exists) {
            ComprobacionInformativa::create(['solicitud_id' => $solicitud->id, 'kb_version' => $r['kb_version'], 'respuesta' => $r]);
        }

        return $r;
    }

    public function camposFormulario(string $tramiteId): array
    {
        return $this->catalogoService->obtenerCamposDeTramite($tramiteId);
    }
}

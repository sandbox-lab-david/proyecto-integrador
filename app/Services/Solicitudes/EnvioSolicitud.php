<?php

namespace App\Services\Solicitudes;

use App\Models\DocumentoFirmado;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\ArchivoService;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Academico\EstudianteService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** Guarda el expediente completo; requiere un adaptador real de identidad/catálogos/estados. */
class EnvioSolicitud
{
    public function __construct(
        private readonly DocumentosTemporales $documentos,
        private readonly FirmadosTemporales $firmados,
        private readonly RespaldosTemporales $respaldos,
        private readonly BorradoresTemporales $borradores,
        private readonly BorradoresSolicitud $borradoresEnCuenta,
        private readonly TramitesSimulados $catalogo,
        private readonly SolicitudService $solicitudes,
        private readonly PrevalidacionSolicitud $prevalidacion,
        private readonly EstudianteService $estudiantes,
    ) {}

    public static function revision(Solicitud $solicitud): string
    {
        return hash('sha256', json_encode([$solicitud->detalle, $solicitud->estado_solicitud_id,
            $solicitud->carrera_estudiante_id, $solicitud->tipo_tramite_id, $solicitud->periodo_id,
            $solicitud->getRawOriginal('updated_at')], JSON_THROW_ON_ERROR));
    }

    public function enviar(User $usuario, array $datos, ?Solicitud $correccion = null, ?string $revisionBase = null): Solicitud
    {
        if ($correccion) {
            abort_unless((int) request()->input('solicitud_correccion') === (int) $correccion->getKey(), 422);
        } else {
            abort_if(request()->filled('solicitud_correccion'), 422);
        }
        if ($correccion) Gate::forUser($usuario)->authorize('update', $correccion);
        $clave = $datos['clave_envio'] ?? null;
        if (! is_string($clave) || ! Str::isUuid($clave)) {
            throw ValidationException::withMessages(['clave_envio' => 'Descarga el documento antes de enviar la solicitud.']);
        }

        $carpeta = 'expedientes/'.Str::uuid();
        $guardada = false;
        try {
            $solicitud = DB::transaction(function () use ($usuario, $datos, $clave, $carpeta, $correccion, $revisionBase, &$guardada) {
                $guardada = false;
                // Serializa los envíos del mismo usuario antes de comprobar el duplicado.
                // La consulta y el expediente se confirman en la misma transacción.
                User::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
                $previa = Solicitud::where('detalle->envio_usuario_id', $usuario->getKey())
                    ->where('detalle->documento_version', $clave)->first();
                if ($previa) {
                    if (! hash_equals($this->huellaEnvio($previa->detalle), $this->huellaEnvio($datos))) {
                        throw ValidationException::withMessages(['clave_envio' => 'Este envío ya se realizó con otros datos. Inicia una nueva solicitud para hacer otro envío.']);
                    }
                    return $previa;
                }

                $documento = $this->documentos->buscar($datos['tramite']);
                if (($documento['version'] ?? null) !== $clave) {
                    throw ValidationException::withMessages(['clave_envio' => 'El documento cambió. Descarga la versión actual y sube su PDF firmado.']);
                }
                $datos['envio_usuario_id'] = $usuario->getKey();
                $solicitud = $this->crearExpediente($usuario, $datos, $carpeta, $correccion, $revisionBase);
                $guardada = true;
                return $solicitud;
            }, 5);
        } catch (\Throwable $error) {
            Storage::disk(ArchivoService::DISCO)->deleteDirectory($carpeta);
            throw $error;
        }

        // Se limpian solo después del commit, sin borrar una versión nueva del formulario.
        $tramite = $datos['tramite'];
        if (($this->documentos->buscar($tramite)['version'] ?? null) === $clave) {
            try {
                foreach ($this->respaldos->delTramite($tramite) as $archivo) $this->respaldos->quitar($archivo['id']);
                $this->firmados->quitar($tramite);
                $this->documentos->quitar($tramite);
                $this->borradores->descartar($tramite);
            } catch (\Throwable $error) {
                report($error);
            }
        }
        // Un reintento idempotente devuelve el expediente sin volver a registrar la comprobación.
        if ($guardada) {
            // Los archivos del borrador en la cuenta ya se copiaron al expediente.
            try {
                Storage::disk(ArchivoService::DISCO)->deleteDirectory(BorradoresSolicitud::carpeta($solicitud));
            } catch (\Throwable $error) {
                report($error);
            }
            // El aviso al estudiante lo envía transicionar() de G3 al confirmar la transacción.
            $this->prevalidacion->registrar($solicitud);
            $this->actualizarCelular($usuario, $datos['celular'] ?? null);
        }
        return $solicitud;
    }

    /** El celular declarado pasa al perfil de G1 (C14) para precargarlo en el próximo trámite. */
    private function actualizarCelular(User $usuario, mixed $celular): void
    {
        try {
            $estudiante = $usuario->estudiante;
            if ($estudiante && is_string($celular) && $celular !== '' && $estudiante->celular !== $celular) {
                $this->estudiantes->actualizarContacto($estudiante, $celular);
            }
        } catch (\Throwable $error) {
            // La solicitud ya está confirmada; el perfil puede actualizarse en el siguiente envío.
            report($error);
        }
    }

    private function huellaEnvio(array $datos): string
    {
        $requisitos = $datos['requisitos'] ?? [];
        ksort($requisitos);
        return hash('sha256', $this->documentos->huella($datos).'|'.json_encode($requisitos, JSON_THROW_ON_ERROR));
    }

    private function crearExpediente(User $usuario, array $datos, string $carpeta, ?Solicitud $correccion = null, ?string $revisionBase = null): Solicitud
    {
        $tramite = $datos['tramite'];
        $documento = $this->documentos->buscar($tramite);
        $firmado = $this->firmados->buscar($tramite);
        if (! $documento || ! $firmado) {
            throw ValidationException::withMessages(['firmado' => 'Descarga el documento y sube su PDF firmado antes de enviar.']);
        }
        if ($documento['hash_datos'] !== $this->documentos->huella($datos)
            || ($firmado['documento_hash'] ?? null) !== $documento['hash_datos']
            || ($firmado['documento_version'] ?? null) !== $documento['version']) {
            throw ValidationException::withMessages(['firmado' => 'Los datos cambiaron. Descarga el documento actualizado y vuelve a subir su PDF firmado.']);
        }
        $archivos = $this->respaldos->delTramite($tramite);
        $asignaciones = $datos['requisitos'] ?? [];
        $requisitos = app(RequisitosSolicitud::class)->verificar($tramite, $archivos, $asignaciones)['documentos'];
        $ids = array_column($archivos, 'id');
        if (array_diff(array_keys($asignaciones), $ids)) {
            throw ValidationException::withMessages(['requisitos' => 'Hay respaldos que no pertenecen a este trámite y sesión.']);
        }
        foreach ($archivos as $archivo) {
            if (! in_array($asignaciones[$archivo['id']] ?? null, array_column($requisitos, 'id'), true)) {
                throw ValidationException::withMessages(['requisitos' => 'Asigna un requisito válido a cada respaldo.']);
            }
        }
        foreach ($requisitos as $requisito) {
            if ($requisito['obligatorio'] && ! in_array($requisito['id'], array_values($asignaciones), true)) {
                throw ValidationException::withMessages(['requisitos' => 'Adjunta el respaldo obligatorio: '.$requisito['nombre']]);
            }
        }
        $integracion = app(IntegracionEnvio::class);
        $contexto = $integracion->resolver($usuario, $datos);
        if ($correccion) {
            foreach (['carrera_estudiante_id', 'tipo_tramite_id', 'periodo_id'] as $clave) {
                abort_unless(($contexto[$clave] ?? null) === (int) $correccion->$clave, 422, 'La corrección no puede cambiar la identidad del expediente, su trámite ni su periodo.');
            }
            abort_unless(($correccion->detalle['tramite'] ?? null) === $tramite, 422);
        }
        foreach (['carrera_estudiante_id', 'tipo_tramite_id', 'periodo_id', 'estado_solicitud_id'] as $clave) {
            if (! is_int($contexto[$clave] ?? null) || $contexto[$clave] < 1) {
                throw new RuntimeException('La integración no devolvió un contexto académico válido.');
            }
        }
        $datos['documento_version'] = $documento['version'];

        $disco = Storage::disk(ArchivoService::DISCO);
        $copiar = function (string $origen, string $nombre) use ($disco, $carpeta): string {
            $destino = $carpeta.'/'.$nombre;
            if (! $disco->exists($origen) || ! $disco->copy($origen, $destino)) {
                throw new RuntimeException('No se pudo conservar un archivo del expediente. Vuelve a adjuntarlo.');
            }
            return $destino;
        };
        try {
            $word = $copiar($documento['ruta'], 'documento-v1.docx');
            // Solo existe si el estudiante descargó el documento en PDF.
            $pdfGenerado = filled($documento['ruta_pdf'] ?? null) ? $copiar($documento['ruta_pdf'], 'documento-v1.pdf') : null;
            $pdf = $copiar($firmado['ruta'], 'firmado-v1.pdf');
            $respaldos = [];
            foreach ($archivos as $archivo) {
                $original = $this->respaldos->buscar($archivo['id']);
                // El requisito elegido al final manda sobre el que se indicó al subir el archivo.
                $respaldos[] = ['ruta_definitiva' => $copiar($original['ruta'], basename($original['ruta'])), 'requisito' => $asignaciones[$archivo['id']]] + $original;
            }
            for ($intento = 0; ; $intento++) {
                try {
                    $solicitud = DB::transaction(function () use ($usuario, $tramite, $datos, $contexto, $integracion, $word, $pdfGenerado, $pdf, $firmado, $respaldos, $correccion, $revisionBase) {
                        $gpa = $datos['campos']['gpa_general'] ?? null;
                        $version = 1;
                        $historialCorrecciones = [];
                        if ($correccion) {
                            $solicitud = Solicitud::lockForUpdate()->findOrFail($correccion->getKey());
                            Gate::forUser($usuario)->authorize('update', $solicitud);
                            abort_unless($revisionBase !== null && hash_equals($revisionBase, self::revision($solicitud)), 409,
                                'La solicitud cambió mientras la corregías. Revisa su estado; descarta el borrador de corrección y vuelve a abrirla con los datos actuales.');
                            $version = ((int) $solicitud->documentosGenerados()->max('version')) + 1;
                            $anterior = $solicitud->detalle;
                            $historialCorrecciones = $anterior['_versiones_anteriores'] ?? [];
                            unset($anterior['_versiones_anteriores']);
                            $historialCorrecciones[] = [
                                'detalle' => $anterior, 'corregida_at' => now()->toIso8601String(),
                                'gpa_verificado' => $solicitud->gpa_verificado,
                                'materias' => $solicitud->materias()->with('registrosAnteriores')->get()->toArray(),
                                'respaldos_ids' => $solicitud->respaldos()->pluck('id')->all(),
                            ];
                            foreach ($solicitud->materias()->get() as $materiaAnterior) {
                                $materiaAnterior->registrosAnteriores()->delete();
                                $materiaAnterior->delete();
                            }
                        }
                        $atributos = [
                            'codigo' => $this->solicitudes->siguienteCodigo(),
                            'carrera_estudiante_id' => $contexto['carrera_estudiante_id'],
                            'tipo_tramite_id' => $contexto['tipo_tramite_id'],
                            'periodo_id' => $contexto['periodo_id'],
                            'estado_solicitud_id' => $contexto['estado_solicitud_id'],
                            'detalle' => $datos + ($correccion ? ['_versiones_anteriores' => $historialCorrecciones] : []),
                            'gpa_declarado' => $gpa === '' ? null : $gpa,
                            'fecha_ultima_recuperacion' => ($datos['campos']['ultima_recuperacion'] ?? '') ?: null,
                            'declaracion_veracidad' => true,
                        ];
                        // El borrador guardado en la cuenta (RF-2.12) se convierte en la solicitud
                        // y conserva su código; si no hay, se crea la fila.
                        $borrador = $correccion ? null
                            : $this->borradoresEnCuenta->consulta($usuario, $tramite)->lockForUpdate()->first();
                        if ($correccion) {
                            unset($atributos['codigo'], $atributos['estado_solicitud_id']);
                            $atributos['gpa_verificado'] = false;
                            $solicitud->forceFill($atributos)->save();
                        } elseif ($borrador) {
                            unset($atributos['codigo'], $atributos['estado_solicitud_id']);
                            // Sus archivos ya se copiaron al expediente: las filas del borrador
                            // se reemplazan por las definitivas (los archivos se borran tras el commit).
                            DocumentoFirmado::whereIn('documento_generado_id', $borrador->documentosGenerados()->select('id'))->delete();
                            $borrador->documentosGenerados()->delete();
                            $borrador->respaldos()->delete();
                            $solicitud = $borrador;
                            $solicitud->forceFill($atributos)->save();
                        } else {
                            $solicitud = Solicitud::create($atributos);
                        }
                        foreach ($datos['materias'] as $materia) {
                            $academicos = $materia['datos'];
                            $fila = $solicitud->materias()->create([
                                // El detalle conserva los datos declarados completos; vincular un curso es opcional.
                                'curso_id' => ($datos['origen_materias'] ?? null) === 'catalogo'
                                    ? (int) $materia['id'] : ($contexto['cursos'][$materia['id']] ?? null),
                                'nota_declarada' => ($academicos['nota'] ?? $academicos['calificacion'] ?? '') === '' ? null : ($academicos['nota'] ?? $academicos['calificacion']),
                                'asistencia_declarada' => ($academicos['asistencia'] ?? '') === '' ? null : $academicos['asistencia'],
                                'estado_declarado' => ($academicos['estado'] ?? '') ?: null,
                            ]);
                            if ($datos['tramite'] === 'tercer-registro') {
                                foreach ([1, 2] as $registro) {
                                    $fila->registrosAnteriores()->create([
                                        'anio' => $academicos['registro_'.$registro.'_anio'],
                                        'periodo_texto' => $academicos['registro_'.$registro.'_periodo'],
                                        'nota' => $academicos['registro_'.$registro.'_nota'],
                                    ]);
                                }
                            }
                        }
                        foreach ($respaldos as $archivo) {
                            $solicitud->respaldos()->create([
                                'documento_requerido_id' => $contexto['requisitos'][$archivo['requisito']] ?? null,
                                'nombre_original' => $archivo['nombre_original'], 'ruta' => $archivo['ruta_definitiva'],
                                'mime' => $archivo['mime'], 'tamano_bytes' => $archivo['tamano_bytes'],
                            ]);
                        }
                        $generado = $solicitud->documentosGenerados()->create(['version' => $version, 'ruta_docx' => $word, 'ruta_pdf' => $pdfGenerado]);
                        $generado->documentoFirmado()->create([
                            'ruta' => $pdf, 'tamano_bytes' => $firmado['tamano_bytes'], 'subido_por' => $usuario->getKey(),
                        ]);
                        $estadoAnterior = $solicitud->estado_solicitud_id;
                        $integracion->enviar($solicitud, $usuario);
                        if ($correccion) {
                            abort_if($solicitud->fresh()->estado_solicitud_id === $estadoAnterior, 409,
                                'El adaptador de revisión no completó la transición. La corrección no se envió.');
                        }
                        $solicitud->forceFill(['enviada_at' => now()])->save();
                        return $solicitud;
                    });
                    break;
                } catch (QueryException $error) {
                    // Solo reintenta colisiones del identificador SOL; otros errores se propagan.
                    if ($intento >= 4 || ! in_array($error->getCode(), ['23000', '23505'], true)
                        || ! str_contains($error->getMessage(), 'codigo')) throw $error;
                }
            }
        } catch (\Throwable $error) {
            $disco->deleteDirectory($carpeta);
            throw $error;
        }
        return $solicitud;
    }
}

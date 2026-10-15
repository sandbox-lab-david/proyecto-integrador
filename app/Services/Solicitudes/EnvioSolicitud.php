<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\ArchivoService;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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
        private readonly TramitesSimulados $catalogo,
        private readonly SolicitudService $solicitudes,
    ) {}

    public function enviar(User $usuario, array $datos): Solicitud
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
        $requisitos = $this->catalogo->buscar($tramite)['documentos'];
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
        foreach (['carrera_estudiante_id', 'tipo_tramite_id', 'periodo_id', 'estado_solicitud_id'] as $clave) {
            if (! is_int($contexto[$clave] ?? null) || $contexto[$clave] < 1) {
                throw new RuntimeException('La integración no devolvió un contexto académico válido.');
            }
        }
        foreach ($datos['materias'] as $materia) {
            if (! isset($contexto['cursos'][$materia['id']])) {
                throw ValidationException::withMessages(['materias' => 'No se pudo vincular una materia con su curso real.']);
            }
        }
        $datos['documento_version'] = $documento['version'];
        $previa = Solicitud::where('carrera_estudiante_id', $contexto['carrera_estudiante_id'])
            ->where('detalle->documento_version', $documento['version'])->first();
        if ($previa) return $previa;
        $disco = Storage::disk(ArchivoService::DISCO);
        $carpeta = 'expedientes/'.Str::uuid();
        $copiar = function (string $origen, string $nombre) use ($disco, $carpeta): string {
            $destino = $carpeta.'/'.$nombre;
            if (! $disco->exists($origen) || ! $disco->copy($origen, $destino)) {
                throw new RuntimeException('No se pudo conservar un archivo del expediente. Vuelve a adjuntarlo.');
            }
            return $destino;
        };
        try {
            $word = $copiar($documento['ruta'], 'documento-v1.docx');
            $pdf = $copiar($firmado['ruta'], 'firmado-v1.pdf');
            $respaldos = [];
            foreach ($archivos as $archivo) {
                $original = $this->respaldos->buscar($archivo['id']);
                $respaldos[] = $original + ['ruta_definitiva' => $copiar($original['ruta'], basename($original['ruta'])), 'requisito' => $asignaciones[$archivo['id']]];
            }
            for ($intento = 0; ; $intento++) {
                try {
                    $solicitud = DB::transaction(function () use ($usuario, $datos, $contexto, $integracion, $word, $pdf, $firmado, $respaldos) {
                        $gpa = $datos['campos']['gpa_periodo'] ?? $datos['campos']['gpa_general'] ?? null;
                        $solicitud = Solicitud::create([
                            'codigo' => $this->solicitudes->siguienteCodigo(),
                            'carrera_estudiante_id' => $contexto['carrera_estudiante_id'],
                            'tipo_tramite_id' => $contexto['tipo_tramite_id'],
                            'periodo_id' => $contexto['periodo_id'],
                            'estado_solicitud_id' => $contexto['estado_solicitud_id'],
                            'detalle' => $datos,
                            'gpa_declarado' => $gpa === '' ? null : $gpa,
                            'fecha_ultima_recuperacion' => ($datos['campos']['ultima_recuperacion'] ?? '') ?: null,
                            'declaracion_veracidad' => true,
                        ]);
                        foreach ($datos['materias'] as $materia) {
                            $academicos = $materia['datos'];
                            $fila = $solicitud->materias()->create([
                                'curso_id' => $contexto['cursos'][$materia['id']],
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
                        $generado = $solicitud->documentosGenerados()->create(['version' => 1, 'ruta_docx' => $word]);
                        $generado->documentoFirmado()->create([
                            'ruta' => $pdf, 'tamano_bytes' => $firmado['tamano_bytes'], 'subido_por' => $usuario->getKey(),
                        ]);
                        $integracion->enviar($solicitud, $usuario);
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
        // Los temporales se conservan si falla cualquier parte de la transacción.
        try {
            foreach ($archivos as $archivo) $this->respaldos->quitar($archivo['id']);
            $this->firmados->quitar($tramite);
            $this->documentos->quitar($tramite);
            $this->borradores->descartar($tramite);
        } catch (\Throwable $error) {
            // El expediente ya se confirmó: un fallo de limpieza no revierte su envío.
            report($error);
        }
        return $solicitud;
    }
}

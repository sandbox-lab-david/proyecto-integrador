<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Solicitud - {{ $solicitud->id ?? 'N/D' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <main class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-4xl mx-auto">

            <!-- Encabezado -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white">Revisión de Solicitud {{ $solicitud->id ?? 'N/D' }}</h2>
                    <p class="text-sm text-slate-400">Detalle y validación documental del caso</p>
                </div>
                <a href="{{ route('revision.asistente') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm px-4 py-2 rounded-lg transition">
                    ← Volver a la Bandeja
                </a>
            </div>

            <!-- Alertas de estado -->
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-900/50 border border-red-700 text-red-200 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tarjeta de Información del Estudiante -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6 shadow-lg">
                <h3 class="text-base font-semibold text-white mb-4">Información del Solicitante</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-400 block">Estudiante:</span>
                        <span class="text-slate-200 font-medium">
                            {{ $solicitud->carreraEstudiante->estudiante->nombre ?? ($solicitud->estudiante ?? 'N/D') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Código / ID:</span>
                        <span class="text-slate-200 font-medium">
                            {{ $solicitud->carreraEstudiante->estudiante->codigo ?? ($solicitud->codigo ?? 'N/D') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Carrera:</span>
                        <span class="text-slate-200 font-medium">
                            {{ $solicitud->carreraEstudiante->nombreCarrera ?? ($solicitud->carrera ?? 'N/D') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Trámite:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->tramite ?? 'N/D' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Estado Actual:</span>
                        <span class="text-indigo-400 font-bold">{{ $solicitud->estadoSolicitud ?? 'Enviado' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Fecha de Registro:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->created_at ?? ($solicitud->fecha ?? 'N/D') }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-800">
                    <span class="text-slate-400 block text-sm mb-1">Motivo de la Solicitud:</span>
                    <p class="text-slate-300 text-sm bg-slate-950 p-3 rounded-lg border border-slate-800/60">
                        {{ $solicitud->motivo ?? 'Sin motivo especificado.' }}
                    </p>
                </div>
            </div>

            <!-- Documentos Adjuntos (Integrados con repositorio de archivos / otros grupos) -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6 shadow-lg">
                <h3 class="text-base font-semibold text-white mb-4">Documentos Adjuntos (Módulo de Archivos)</h3>
                @if(!empty($solicitud->documentos) && is_array($solicitud->documentos) && count($solicitud->documentos) > 0)
                    <div class="space-y-3">
                        @foreach($solicitud->documentos as $doc)
                            <div class="flex items-center justify-between bg-slate-950 p-3 rounded-lg border border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-slate-200">{{ is_object($doc) ? ($doc->nombre ?? 'Documento') : $doc }}</p>
                                        <p class="text-xs text-slate-400">PDF Adjunto por Estudiante / Sistema</p>
                                    </div>
                                </div>
                                <a href="{{ is_object($doc) && isset($doc->url) ? $doc->url : '#' }}" target="_blank" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-md transition">Ver / Descargar</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">No hay documentos registrados para este caso o el repositorio externo no devolvió archivos.</p>
                @endif
            </div>

            <!-- Formulario de Revisión (Bloqueado si ya está finalizado) -->
            @php
                $esFinalizado = in_array($solicitud->estadoSolicitud ?? '', ['Aprobado', 'Rechazado']);
            @endphp

            <form action="{{ route('revision.guardar', $solicitud->id) }}" method="POST" class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg" onsubmit="return validarAccion(event);">
                @csrf
                <h3 class="text-sm font-semibold text-white mb-4">Registrar Revisión y Decisión</h3>

                <div class="space-y-3 mb-6">
                    <label class="flex items-center space-x-3 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="check_documento" value="1" {{ $esFinalizado ? 'disabled' : '' }} class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Documento legible y válido</span>
                    </label>
                    <label class="flex items-center space-x-3 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="check_datos" value="1" {{ $esFinalizado ? 'disabled' : '' }} class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Datos del estudiante coinciden con el expediente</span>
                    </label>
                    <label class="flex items-center space-x-3 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" name="check_requisitos" value="1" {{ $esFinalizado ? 'disabled' : '' }} class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Cumple con los requisitos normativos de la facultad</span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Observaciones del Asistente</label>
                    <textarea name="observaciones" rows="3" {{ $esFinalizado ? 'disabled' : '' }} class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Escriba sus observaciones o motivo de devolución..."></textarea>
                </div>

                <!-- 3 Botones de Decisión con confirmación -->
                @unless($esFinalizado)
                <div class="flex justify-end space-x-3">
                    <button type="submit" name="accion" value="rechazar" onclick="window.accionSeleccionada = 'rechazar'" class="bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-600/40 px-4 py-2 rounded-lg text-sm transition">
                        Rechazar Solicitud
                    </button>
                    <button type="submit" name="accion" value="observado" onclick="window.accionSeleccionada = 'observado'" class="bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-600/40 px-4 py-2 rounded-lg text-sm transition">
                        Marcar como Observado
                    </button>
                    <button type="submit" name="accion" value="aprobar" onclick="window.accionSeleccionada = 'aprobar'" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-sm font-medium transition shadow-lg shadow-indigo-600/20">
                        Aprobar y Enviar a Consejo
                    </button>
                </div>
                @else
                <div class="p-4 bg-slate-950 rounded-lg border border-slate-800 text-center text-sm text-slate-300">
                    🔒 Este caso ya se encuentra <span class="font-bold text-white uppercase">{{ $solicitud->estadoSolicitud }}</span> y está bloqueado para edición.
                </div>
                @endunless
            </form>

        </div>
    </main>

    <!-- Script de confirmación -->
    <script>
        function validarAccion(event) {
            let accion = window.accionSeleccionada;
            let mensaje = "¿Está seguro de realizar esta acción?";
            
            if (accion === 'aprobar') {
                mensaje = "¿Está completamente seguro de APROBAR esta solicitud y enviarla a Consejo?";
            } else if (accion === 'rechazar') {
                mensaje = "¿Está seguro de RECHAZAR esta solicitud? Esta acción finalizará el trámite.";
            } else if (accion === 'observado') {
                mensaje = "¿Desea marcar este caso como OBSERVADO para que el estudiante corrija los datos?";
            }

            if (!confirm(mensaje)) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
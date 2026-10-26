@extends('layouts.revision')

@section('page-title', 'Revisión de solicitud')
@section('page-heading') Revisión de Solicitud {{ $solicitud->id ?? 'N/D' }} @endsection
@section('page-description', 'Detalle y validación documental del caso')
@section('active-page', 'detail')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <!-- Alertas de estado -->
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tarjeta de Información del Estudiante -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6 shadow-lg">
                <h3 class="text-base font-semibold text-white mb-4">Información del Solicitante</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-400 block">Estudiante:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->estudiante ?? 'N/D' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Código / ID:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->codigo ?? 'N/D' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Carrera:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->carrera ?? 'N/D' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Trámite:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->tramite ?? 'N/D' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Estado Actual:</span>
                        <span class="text-indigo-400 font-bold">{{ $solicitud->estado ?? 'Enviado por estudiante' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Fecha de Envío:</span>
                        <span class="text-slate-200 font-medium">{{ $solicitud->fecha ?? 'N/D' }}</span>
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
                $esFinalizado = ($solicitud->estado ?? '') !== 'Enviado por estudiante';
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

                <!-- Confirmación de envío al Director -->
                @unless($esFinalizado)
                <div class="flex flex-wrap justify-end gap-3">
                    <button type="submit" name="accion" value="observar" onclick="window.accionSeleccionada = 'observar'" class="rounded-lg border border-amber-300 bg-amber-50 px-5 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                        Marcar como Observado
                    </button>
                    <button type="submit" name="accion" value="rechazar" onclick="window.accionSeleccionada = 'rechazar'" class="rounded-lg border border-red-300 bg-white px-5 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">
                        Rechazar
                    </button>
                    <button type="submit" name="accion" value="aprobar" onclick="window.accionSeleccionada = 'aprobar'" class="rounded-lg bg-red-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-800">
                        Enviar al director
                    </button>
                </div>
                @else
                <div class="p-4 bg-slate-950 rounded-lg border border-slate-800 text-center text-sm text-slate-300">
                    🔒 Este caso ya se encuentra <span class="font-bold text-white uppercase">{{ $solicitud->estado }}</span> y está bloqueado para edición.
                </div>
                @endunless
            </form>


    <script>
        function validarAccion(event) {
            let accion = window.accionSeleccionada;
            let mensaje = "¿Está seguro de realizar esta acción?";
            
            if (accion === 'aprobar') {
                mensaje = "¿Está seguro de enviar esta solicitud al Director?";
            } else if (accion === 'observar') {
                mensaje = "¿Está seguro de marcar esta solicitud como Observado?";
            } else if (accion === 'rechazar') {
                mensaje = "¿Está seguro de marcar esta solicitud como Rechazado?";
            }

            if (!confirm(mensaje)) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endsection

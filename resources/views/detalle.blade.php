<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Solicitud - Módulo 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex">

    <aside class="w-64 bg-slate-900 border-r border-slate-800 p-6 flex flex-col justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-wider text-white mb-8">UEES Solicitudes <span class="text-xs bg-indigo-600 px-2 py-1 rounded text-white ml-2">M3</span></h1>
            <nav class="space-y-3">
                <a href="{{ route('revision.asistente') }}" class="block px-4 py-2.5 rounded-lg bg-indigo-600 text-white font-medium">Bandeja Asistente</a>
                <a href="#" class="block px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">Bandeja Director</a>
                <a href="#" class="block px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">Agendas Consejo</a>
                <a href="#" class="block px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">Resoluciones</a>
            </nav>
        </div>
        <div class="text-xs text-slate-500">Módulo de Revisión y Consejo</div>
    </aside>

    <main class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white">Revisión de Solicitud #{{ $solicitud->id }}</h2>
                    <p class="text-sm text-slate-400">Detalle y validación documental del caso</p>
                </div>
                <a href="{{ route('revision.asistente') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg text-sm font-medium transition">
                    &larr; Volver a la Bandeja
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6 shadow-xl space-y-4">
                <h3 class="text-sm font-semibold text-indigo-400 uppercase tracking-wider mb-2">Información del Estudiante</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-400 block text-xs">Estudiante</span>
                        <span class="font-medium text-white text-base">{{ $solicitud->estudiante }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Código</span>
                        <span class="font-mono text-slate-300">{{ $solicitud->codigo }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Carrera</span>
                        <span class="text-slate-300">{{ $solicitud->carrera }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Trámite</span>
                        <span class="text-slate-300">{{ $solicitud->tramite }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Materia</span>
                        <span class="text-slate-300">{{ $solicitud->materia ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Estado Actual</span>
                        @php
                            $estadoClase = match($solicitud->estado) {
                                'Aplica' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'Requiere corrección' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'Rechazado' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                default => 'bg-sky-500/10 text-sky-400 border-sky-500/20'
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $estadoClase }} inline-block mt-1">
                            {{ $solicitud->estado }}
                        </span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800">
                    <span class="text-slate-400 block text-xs mb-1">Motivo de la Solicitud</span>
                    <p class="text-sm text-slate-300 bg-slate-950 p-3 rounded-lg border border-slate-800/60">{{ $solicitud->motivo }}</p>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6 shadow-xl">
                <h3 class="text-sm font-semibold text-indigo-400 uppercase tracking-wider mb-4">Documento del Expediente</h3>
                <div class="flex items-center justify-between bg-slate-950 p-4 rounded-lg border border-slate-800/60">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <span class="text-sm font-medium text-white block">Expediente_Solicitud_{{ $solicitud->id }}.pdf</span>
                            <span class="text-xs text-slate-400">Documento adjunto por el estudiante</span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('documento.descargar', 'CARRERA.pdf') }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-xs font-semibold transition shadow-md">
                            Ver / Descargar PDF
                        </a>
                    </div>
                </div>
            </div>

            @php
                $esFinalizado = in_array($solicitud->estado, ['Aplica', 'Rechazado', 'Aprobado']);
            @endphp

            <form action="{{ route('revision.caso.decidir', $solicitud->id) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl shadow-xl">
                    <h3 class="text-sm font-semibold text-white mb-4">Registrar Revisión y Decisión</h3>
                    
                    <div class="space-y-3 mb-6">
                        <label class="flex items-center space-x-3 text-sm text-slate-300 cursor-pointer">
                            <input type="checkbox" name="check_documento" value="1" 
                                {{ old('check_documento', $solicitud->check_documento) ? 'checked' : '' }} 
                                {{ $esFinalizado ? 'disabled' : '' }}
                                class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>Documento legible y válido</span>
                        </label>

                        <label class="flex items-center space-x-3 text-sm text-slate-300 cursor-pointer">
                            <input type="checkbox" name="check_datos" value="1" 
                                {{ old('check_datos', $solicitud->check_datos) ? 'checked' : '' }} 
                                {{ $esFinalizado ? 'disabled' : '' }}
                                class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>Datos del estudiante coinciden con el expediente</span>
                        </label>

                        <label class="flex items-center space-x-3 text-sm text-slate-300 cursor-pointer">
                            <input type="checkbox" name="check_requisitos" value="1" 
                                {{ old('check_requisitos', $solicitud->check_requisitos) ? 'checked' : '' }} 
                                {{ $esFinalizado ? 'disabled' : '' }}
                                class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>Requisitos institucionales completos</span>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs text-slate-400 mb-2">Observaciones Internas / Motivo de Rechazo</label>
                        <textarea name="observaciones_internas" rows="3" 
                            {{ $esFinalizado ? 'disabled' : '' }}
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500" 
                            placeholder="Ingrese notas u observaciones sobre la revisión...">{{ old('observaciones_internas', $solicitud->observaciones_internas) }}</textarea>
                    </div>

                    @if($esFinalizado)
                        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm font-medium">
                            Este caso ya se encuentra procesado (Estado: {{ $solicitud->estado }}). Las opciones de modificación están bloqueadas.
                        </div>
                    @else
                        <div class="flex flex-wrap gap-4">
                            <button type="submit" name="decision" value="aplica" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-md">
                                Aprobar / Aplica
                            </button>
                            <button type="submit" name="decision" value="correccion" class="bg-amber-600 hover:bg-amber-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-md">
                                Requiere Corrección
                            </button>
                            <button type="submit" name="decision" value="rechaza" onclick="return confirm('¿Está seguro de rechazar esta solicitud?')" class="bg-rose-600 hover:bg-rose-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-md">
                                No aplica / Rechazar
                            </button>
                        </div>
                    @endif
                </div>
            </form>

        </div>
    </main>

</body>
</html>
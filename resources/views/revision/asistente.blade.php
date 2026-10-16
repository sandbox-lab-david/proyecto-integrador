<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Asistente - Módulo de Revisión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex">

    <!-- Barra Lateral -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 p-6 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="mb-8">
                <h1 class="text-xl font-bold text-white tracking-wide">UEES Solicitudes</h1>
                <span class="text-xs bg-indigo-600 text-white px-2 py-0.5 rounded-md font-semibold">M3</span>
            </div>
            <nav class="space-y-2">
                <a href="{{ route('revision.asistente') }}" class="block px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium">Bandeja Asistente</a>
                <a href="#" class="block px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white text-sm transition">Bandeja Director</a>
                <a href="#" class="block px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white text-sm transition">Agendas Consejo</a>
                <a href="#" class="block px-4 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white text-sm transition">Resoluciones</a>
            </nav>
        </div>
        <div class="text-xs text-slate-500">
            Módulo de Revisión y Consejo
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-slate-900 border-b border-slate-800 px-8 py-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">Bandeja de Solicitudes - Asistente de Facultad</h2>
                <p class="text-xs text-slate-400 mt-1">Primer filtro de revisión documental de casos</p>
            </div>
            <div class="text-xs bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300">
                Facultad: <span class="font-medium text-white">Ingeniería</span>
            </div>
        </header>

        <main class="p-8 overflow-y-auto">
            <!-- Filtros -->
            <form method="GET" action="{{ route('revision.asistente') }}" class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-8 shadow-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs text-slate-400 mb-2">Filtrar por Trámite</label>
                        <select name="tramite" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Todos los trámites --</option>
                            @if(isset($tramitesDisponibles))
                                @foreach($tramitesDisponibles as $t)
                                    <option value="{{ $t }}" {{ ($filtroTramite ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-2">Filtrar por Estado</label>
                        <select name="estado" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Todos los estados --</option>
                            @if(isset($estadosDisponibles))
                                @foreach($estadosDisponibles as $e)
                                    <option value="{{ $e }}" {{ ($filtroEstado ?? '') == $e ? 'selected' : '' }}>{{ $e }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('revision.asistente') }}" class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-center py-2.5 rounded-lg text-sm transition">Limpiar</a>
                        <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2.5 rounded-lg text-sm transition shadow-lg shadow-indigo-600/20">Filtrar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Solicitudes -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs text-slate-400 uppercase bg-slate-950/50">
                            <th class="p-4 font-semibold">ID Solicitud</th>
                            <th class="p-4 font-semibold">Estudiante</th>
                            <th class="p-4 font-semibold">Carrera</th>
                            <th class="p-4 font-semibold">Trámite</th>
                            <th class="p-4 font-semibold">Estado</th>
                            <th class="p-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-sm">
                        @forelse($solicitudes as $solicitud)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4 font-medium text-indigo-400">
                                    <a href="{{ route('revision.detalle', $solicitud->id) }}" class="hover:underline">
                                        {{ $solicitud->id }}
                                    </a>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-200">{{ $solicitud->estudiante ?? 'N/D' }}</div>
                                    <div class="text-xs text-slate-400">{{ $solicitud->codigo ?? 'N/D' }}</div>
                                </td>
                                <td class="p-4 text-slate-300">{{ $solicitud->carrera ?? 'N/D' }}</td>
                                <td class="p-4 text-slate-300">{{ $solicitud->tramite ?? 'N/D' }}</td>
                                <td class="p-4">
                                    @php
                                        $estado = $solicitud->estado ?? 'Enviado';
                                        $claseEstado = match($estado) {
                                            'Aprobado' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'Rechazado' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'Observado' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            default => 'bg-sky-500/10 text-sky-400 border-sky-500/20'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium border {{ $claseEstado }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('revision.detalle', $solicitud->id) }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg text-xs font-medium transition inline-block border border-slate-700">
                                        Revisar Caso
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 text-sm">
                                    No se encontraron solicitudes registradas en la base de datos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
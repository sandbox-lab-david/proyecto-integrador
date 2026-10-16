<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Asistente - Módulo 3</title>
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
        <div class="max-w-7xl mx-auto">
            
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-white">Bandeja de Solicitudes - Asistente de Facultad</h2>
                    <p class="text-sm text-slate-400">Primer filtro de revisión documental de casos</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-sm text-slate-300">
                    Facultad: <span class="text-indigo-400 font-medium">Ingeniería</span>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl mb-8 shadow-xl">
                <form method="GET" action="{{ route('revision.asistente') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-slate-400 mb-2">Filtrar por Trámite</label>
                            <select name="tramite" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Todos los trámites --</option>
                                @foreach($tramitesDisponibles as $t)
                                    <option value="{{ $t }}" {{ request('tramite') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-400 mb-2">Filtrar por Estado</label>
                            <select name="estado" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Todos los estados --</option>
                                @foreach($estadosDisponibles as $e)
                                    <option value="{{ $e }}" {{ request('estado') == $e ? 'selected' : '' }}>{{ $e }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-400 mb-2">Buscar Estudiante o Código</label>
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej. Carlos o U2023..." class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('revision.asistente') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg text-sm font-medium transition">Limpiar</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-sm font-semibold transition shadow-md">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
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
                                <td class="p-4 font-mono text-indigo-400">{{ $solicitud->id }}</td>
                                <td class="p-4">
                                    <div class="font-medium text-white">{{ $solicitud->estudiante }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $solicitud->codigo }}</div>
                                </td>
                                <td class="p-4 text-slate-300">{{ $solicitud->carrera }}</td>
                                <td class="p-4 text-slate-300">{{ $solicitud->tramite }}</td>
                                <td class="p-4">
                                    @php
                                        $estadoClase = match($solicitud->estado) {
                                            'Aplica' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'Requiere corrección' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            default => 'bg-sky-500/10 text-sky-400 border-sky-500/20'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $estadoClase }}">
                                        {{ $solicitud->estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('revision.caso.detalle', $solicitud->id) }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3.5 py-1.5 rounded-lg text-xs font-medium transition border border-slate-700">
                                        Revisar Caso
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    No se encontraron solicitudes registradas con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </main>

</body>
</html>
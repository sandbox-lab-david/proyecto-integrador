@extends('layouts.revision')

@section('page-title', 'Resoluciones')
@section('page-heading', isset($agenda) ? 'Resoluciones de '.$agenda->numero : 'Resoluciones')
@section('page-description', isset($agenda) ? 'Decisiones por expediente de esta agenda' : 'Selecciona una agenda para consultar sus expedientes')
@section('active-page', 'resoluciones')

@section('content')
    @if(session('success'))<p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>@endif

    @if(isset($agenda))
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div><h2 class="font-semibold text-slate-900">{{ $agenda->numero }}</h2><p class="text-sm text-slate-600">Sesión: {{ \Illuminate\Support\Carbon::parse($agenda->fecha_sesion)->format('d/m/Y') }}</p></div>
            <div class="flex gap-2"><a href="{{ route('revision.agendas.documento',$agenda->id) }}" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Descargar agenda Word</a></div>
        </div>
        @if($agenda->puntosInternos->isNotEmpty())<section class="mb-5 rounded-xl border border-slate-200 bg-white p-4"><h2 class="mb-2 font-semibold text-slate-900">Puntos internos</h2>@foreach($agenda->puntosInternos as $punto)<p class="text-sm text-slate-700"><strong>{{ $punto->titulo }}:</strong> {{ $punto->descripcion }}</p>@endforeach</section>@endif
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @forelse($casos as $caso)
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 p-4 last:border-b-0">
                    <div class="min-w-56 flex-1">
                        <p class="font-semibold text-slate-900">Expediente {{ $caso->codigo_solicitud }} · {{ $caso->estudiante }}</p>
                        <p class="text-sm text-slate-600">{{ $caso->tramite }} · {{ $caso->estado_solicitud }} · {{ $caso->carrera }}</p>
                        <p class="text-xs text-slate-500">Código CAFI: {{ $caso->codigo_sistema ?: '—' }} · Sesión hace {{ (int) \Illuminate\Support\Carbon::parse($agenda->fecha_sesion)->diffInDays(now()) }} días</p>
                        @if($caso->numero_resolucion)<p class="mt-1 text-xs text-slate-500">Resolución {{ $caso->numero_resolucion }}</p>@endif
                    </div>
                    @if($caso->decision)
                        <span @class(['rounded-full border px-3 py-1 text-sm font-semibold', 'border-emerald-200 bg-emerald-50 text-emerald-800' => $caso->decision === 'Aprobado', 'border-red-200 bg-red-50 text-red-800' => $caso->decision === 'Rechazado', 'border-amber-200 bg-amber-50 text-amber-800' => $caso->decision === 'Guardar'])>{{ $caso->decision }}</span>
                    @else
                        <span class="rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800">Pendiente de resolución</span>
                    @endif
                </div>
            @empty
                <p class="p-6 text-slate-500">Esta agenda todavía no tiene expedientes.</p>
            @endforelse
        </div>
    @else
        <form method="GET" action="{{ route('revision.resoluciones') }}" autocomplete="off" class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <input type="hidden" name="filtrar" value="1">
            <div class="grid items-end gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div><label for="nombre" class="mb-2 block text-xs font-medium text-slate-600">Nombre del estudiante</label><input id="nombre" name="nombre" type="search" autocomplete="off" value="{{ $filtros['nombre'] }}" placeholder="Buscar por nombre" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"></div>
                <div><label for="codigo" class="mb-2 block text-xs font-medium text-slate-600">Código de estudiante</label><input id="codigo" name="codigo" type="search" value="{{ $filtros['codigo'] }}" placeholder="Buscar por código SIAC" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"></div>
                <div><label for="carrera" class="mb-2 block text-xs font-medium text-slate-600">Carrera</label><input id="carrera" name="carrera" type="search" value="{{ $filtros['carrera'] }}" placeholder="Buscar por carrera" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"></div>
                <div><label for="tramite" class="mb-2 block text-xs font-medium text-slate-600">Trámite</label><select id="tramite" name="tramite" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"><option value="">Todos los trámites</option>@foreach($tramitesDisponibles as $tramite)<option value="{{ $tramite }}" @selected($filtros['tramite'] === $tramite)>{{ $tramite }}</option>@endforeach</select></div>
                <div><label for="estado" class="mb-2 block text-xs font-medium text-slate-600">Estado de solicitud</label><select id="estado" name="estado" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"><option value="">Todos los estados</option>@foreach($estadosDisponibles as $estado)<option value="{{ $estado }}" @selected($filtros['estado'] === $estado)>{{ $estado }}</option>@endforeach</select></div>
                <div class="grid grid-cols-2 gap-3"><div><label for="desde" class="mb-2 block text-xs font-medium text-slate-600">Desde</label><input id="desde" name="desde" type="date" value="{{ $filtros['desde'] }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"></div><div><label for="hasta" class="mb-2 block text-xs font-medium text-slate-600">Hasta</label><input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"></div></div>
                <div class="flex gap-2 md:col-span-2 xl:col-span-3"><a href="{{ route('revision.resoluciones') }}" class="w-1/3 rounded-lg border border-slate-300 py-2.5 text-center text-sm text-slate-700 hover:bg-slate-50">Limpiar</a><button class="w-2/3 rounded-lg bg-red-700 py-2.5 text-sm font-semibold text-white hover:bg-red-800">Filtrar agendas</button></div>
            </div>
            @if($errors->any())<p class="mt-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
        </form>

        <div class="mb-3 flex flex-wrap items-center justify-between gap-3"><h2 class="text-xl font-semibold text-slate-900">Agendas con solicitudes coincidentes</h2>@if($filtrosAplicados)<a href="{{ route('revision.resoluciones.documento', array_filter($filtros, fn ($valor) => $valor !== '' && $valor !== false)) }}" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Documento de agendas filtradas</a>@endif</div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full border-collapse text-left">
                <thead><tr class="border-b border-slate-200 bg-[#f4f3ef] text-xs uppercase text-slate-600"><th class="p-4">Agenda</th><th class="p-4">Fecha de sesión</th><th class="p-4">Días transcurridos</th><th class="p-4">Estado de agenda</th><th class="p-4">Estados de solicitudes</th><th class="p-4">Resoluciones pendientes</th><th class="p-4 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse($agendas as $item)
                        <tr class="hover:bg-[#faf9f6]">
                            <td class="p-4 font-semibold text-slate-900">{{ $item->numero }}<div class="text-xs font-normal text-slate-500">{{ $item->total_solicitudes }} solicitudes</div></td>
                            <td class="p-4 text-slate-700">{{ \Illuminate\Support\Carbon::parse($item->fecha_sesion)->format('d/m/Y') }}</td>
                            <td class="p-4 text-slate-700">{{ (int) \Illuminate\Support\Carbon::parse($item->fecha_sesion)->diffInDays(now()) }}</td>
                            <td class="p-4"><span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-800">{{ $item->estado }}</span></td>
                            <td class="p-4"><div class="flex flex-wrap gap-1.5">@foreach([['Enviado por estudiante', $item->total_enviadas], ['Revisado', $item->total_revisadas], ['Apto para consejo', $item->total_aptas_consejo], ['Agendado', $item->total_agendadas], ['Aprobado para ejecución', $item->total_aprobadas_ejecucion], ['Ejecutado', $item->total_ejecutadas], ['Observado', $item->total_observadas], ['Rechazado', $item->total_rechazadas]] as [$etiqueta, $cantidad]) @if($cantidad > 0)<span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs text-slate-700">{{ $etiqueta }}: {{ $cantidad }}</span>@endif @endforeach</div></td>
                            <td class="p-4">@if($item->total_pendientes_resolucion > 0)<span class="rounded-full border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $item->total_pendientes_resolucion }} pendiente(s)</span>@else<span class="text-emerald-700">Al día</span>@endif</td>
                            <td class="p-4 text-right"><a href="{{ route('revision.agendas.resoluciones', $item->id) }}" class="rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-800">Resoluciones</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-slate-500">@if($filtrosAplicados)No hay agendas para los filtros seleccionados.@elseUsa los filtros y presiona “Filtrar agendas” para consultar los resultados.@endif</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    @endif
@endsection

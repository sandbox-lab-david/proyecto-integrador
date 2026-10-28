@extends('layouts.revision')

@section('page-title', 'Bandeja Director')
@section('page-heading', 'Bandeja del Director')
@section('page-description', 'Casos enviados para decisión del Director')
@section('active-page', 'director')

@section('content')
    @if(session('success'))<p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p>@endif
    @include('revision.partials.filtros-solicitudes', ['formAction' => route('revision.director')])

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="w-full text-left">
            <thead><tr class="border-b border-slate-200 bg-[#f4f3ef] text-xs uppercase text-slate-600"><th class="p-4">Solicitud</th><th class="p-4">Estudiante</th><th class="p-4">Carrera</th><th class="p-4">Trámite</th><th class="p-4">Estado</th><th class="p-4 text-right">Decisión</th></tr></thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($solicitudes as $caso)
                    <tr class="hover:bg-[#faf9f6]">
                        <td class="p-4 font-semibold text-red-800"><a href="{{ route('revision.detalle',$caso->id) }}" class="hover:underline">{{ $caso->codigo_solicitud }}</a></td>
                        <td class="p-4 text-slate-800">{{ $caso->estudiante }}<small class="block text-slate-500">{{ $caso->codigo_siac }}</small></td>
                        <td class="p-4 text-slate-700">{{ $caso->carrera }}</td>
                        <td class="p-4 text-slate-700">{{ $caso->tramite }}</td>
                        <td class="p-4"><span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700">{{ $caso->estado }}</span></td>
                        <td class="p-4 text-right">
                            @if($caso->estado === 'Revisado')
                                <div class="flex flex-wrap justify-end gap-2"><a href="{{ route('revision.detalle',$caso->id) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Ver expediente</a><form method="POST" action="{{ route('revision.director.guardar', $caso->id) }}" class="flex flex-wrap justify-end gap-2">@csrf @foreach($filtros as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ is_bool($value) ? (int) $value : $value }}">@endforeach<input name="observacion" placeholder="Observación (opcional)" class="min-w-40 rounded-lg border border-slate-300 px-2 py-1.5 text-sm"><button name="accion" value="aprobar" class="rounded-lg bg-red-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-800">Apto para consejo</button><button name="accion" value="rechazar" class="rounded-lg border border-red-300 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Rechazar</button></form></div>
                            @else
                                <span class="text-xs text-slate-500">Sin acciones para este estado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">
                            @if($filtrosAplicados)
                                No hay solicitudes que coincidan con los filtros.
                            @else
                                Usa los filtros y presiona “Filtrar” para consultar las solicitudes.
                            @endif
                        </td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
@endsection

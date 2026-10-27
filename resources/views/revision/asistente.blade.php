@extends('layouts.revision')

@section('page-title', 'Bandeja de solicitudes')
@section('page-heading', 'Bandeja de Solicitudes - Asistente de Facultad')
@section('page-description', 'Primer filtro de revisión documental de casos')
@section('active-page', 'assistant')

@section('content')
    @include('revision.partials.filtros-solicitudes', ['formAction' => route('revision.asistente')])

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-[#f4f3ef] text-xs uppercase text-slate-600">
                        <th class="p-4 font-semibold">ID solicitud</th>
                        <th class="p-4 font-semibold">Estudiante</th>
                        <th class="p-4 font-semibold">Carrera</th>
                        <th class="p-4 font-semibold">Trámite</th>
                        <th class="p-4 font-semibold">Estado</th>
                        <th class="p-4 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse($solicitudes as $solicitud)
                        <tr class="transition hover:bg-[#faf9f6]">
                            <td class="p-4 font-medium text-red-800"><a href="{{ route('revision.detalle', $solicitud->id) }}" class="hover:underline">{{ $solicitud->id }}</a></td>
                            <td class="p-4"><div class="font-medium text-slate-900">{{ $solicitud->estudiante ?? 'N/D' }}</div><div class="text-xs text-slate-500">{{ $solicitud->codigo ?? 'N/D' }}</div></td>
                            <td class="p-4 text-slate-700">{{ $solicitud->carrera ?? 'N/D' }}</td>
                            <td class="p-4 text-slate-700">{{ $solicitud->tramite ?? 'N/D' }}</td>
                            <td class="p-4">
                                @php
                                    $estado = $solicitud->estado ?? 'Enviado por estudiante';
                                    $claseEstado = match($estado) {
                                        'Ejecutado' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                        'Observado' => 'border-amber-200 bg-amber-50 text-amber-800',
                                        'Rechazado' => 'border-red-300 bg-red-100 text-red-900',
                                        'Aprobado para ejecución' => 'border-green-200 bg-green-50 text-green-800',
                                        'Apto para consejo' => 'border-red-200 bg-red-50 text-red-800',
                                        'Agendado' => 'border-amber-200 bg-amber-50 text-amber-800',
                                        'Revisado' => 'border-violet-200 bg-violet-50 text-violet-800',
                                        default => 'border-sky-200 bg-sky-50 text-sky-800'
                                    };
                                @endphp
                                <span class="rounded-full border px-2.5 py-1 text-xs font-medium {{ $claseEstado }}">{{ $estado }}</span>
                            </td>
                            <td class="p-4 text-right"><a href="{{ route('revision.detalle', $solicitud->id) }}" class="inline-block rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-red-700 hover:text-red-800">Revisar caso</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-sm text-slate-500">@if($filtrosAplicados) No se encontraron solicitudes que coincidan con los filtros. @else Usa los filtros y presiona “Filtrar” para consultar las solicitudes. @endif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

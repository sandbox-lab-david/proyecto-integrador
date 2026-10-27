@extends('layouts.revision')

@section('page-title', 'Agendas del Consejo')
@section('page-heading', 'Agendas del Consejo')
@section('page-description', 'Selección de solicitudes aptas para el Consejo')
@section('active-page', 'agendas')

@section('content')
    @if(session('success'))<p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</p>@endif

    @include('revision.partials.filtros-solicitudes', ['formAction' => route('revision.agendas')])

    @if(app(\App\Services\Academico\AutorizacionService::class)->tieneRol(auth()->user(), 'admin'))
    <form id="crear-agenda" method="POST" action="{{ route('revision.agendas.store') }}" class="mb-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <h2 class="mb-4 text-xl font-semibold text-slate-900">Crear agenda con solicitudes aptas para Consejo</h2>
        <label class="block text-sm text-slate-700">Fecha de sesión <input type="date" name="fecha_sesion" min="{{ now()->toDateString() }}" required value="{{ old('fecha_sesion') }}" class="ml-2 rounded-lg border border-slate-300 bg-white p-2"></label>
        <div class="mt-4 grid gap-3 md:grid-cols-2"><label class="text-sm text-slate-700">Punto interno (opcional)<input name="punto_titulo" value="{{ old('punto_titulo') }}" class="mt-1 w-full rounded-lg border border-slate-300 p-2" placeholder="Ej. Lectura del acta anterior"></label><label class="text-sm text-slate-700">Descripción<textarea name="punto_descripcion" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2" placeholder="Detalle del punto"></textarea></label></div>
        @foreach($filtros as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ is_bool($value) ? (int) $value : $value }}">@endforeach

    <div class="mb-8 grid gap-2 md:grid-cols-2">
        @forelse($aprobadas as $caso)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white p-3">
                <div class="flex min-w-56 flex-1 items-start gap-2 text-slate-800"><input id="agenda-solicitud-{{ $caso->id }}" form="crear-agenda" type="checkbox" name="solicitudes[]" value="{{ $caso->id }}" @checked(in_array($caso->id, old('solicitudes', []))) class="mt-1"><label for="agenda-solicitud-{{ $caso->id }}"><b>{{ $caso->codigo_solicitud }}</b> · {{ $caso->estudiante }}<small class="block text-slate-500">{{ $caso->tramite }} — {{ $caso->carrera }} — {{ $caso->estado }} · CAFI {{ $caso->codigo_sistema ?: 'pendiente' }}</small></label><a href="{{ route('revision.detalle',$caso->id) }}" class="text-xs text-red-800 underline">Expediente</a></div>
            </article>
        @empty
            <p class="rounded-lg border border-slate-200 bg-white p-4 text-slate-500">No hay solicitudes aptas que coincidan con los filtros y estén pendientes de agenda.</p>
        @endforelse
    </div>
    <div class="mb-8 flex flex-wrap justify-end gap-2">
        <button type="submit" name="accion" value="rechazar" formaction="{{ route('revision.agendas.solicitudes.rechazar-varias') }}" formnovalidate @disabled($aprobadas->isEmpty()) onclick="return confirm('¿Rechazar todas las solicitudes seleccionadas?')" class="rounded-lg border border-red-300 bg-white px-4 py-2 font-semibold text-red-700 transition hover:bg-red-50 disabled:opacity-40">Rechazar seleccionadas</button>
        <button type="submit" name="accion" value="crear_agenda" @disabled($aprobadas->isEmpty()) class="rounded-lg bg-red-700 px-4 py-2 font-semibold text-white transition hover:bg-red-800 disabled:opacity-40">Crear agenda</button>
    </div>
    </form>
    @endif

@endsection

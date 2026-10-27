@extends('layouts.revision')

@section('page-title', 'Revisión de solicitud')
@section('page-heading', 'Expediente '.$solicitud->codigo)
@section('page-description', 'Detalle del expediente, documentos, verificación y trazabilidad')
@section('active-page', 'detail')

@section('content')
    @if(session('success'))<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>@endif

    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Expediente y estudiante</h2>
        <dl class="grid gap-4 text-sm md:grid-cols-2 xl:grid-cols-3">
            <div><dt class="text-slate-500">Estudiante</dt><dd class="font-medium text-slate-900">{{ $solicitud->estudiante ?: 'N/D' }} · {{ $solicitud->codigo_siac ?: 'sin código' }}</dd></div>
            <div><dt class="text-slate-500">Carrera</dt><dd class="font-medium text-slate-900">{{ $solicitud->carrera ?: 'N/D' }}</dd></div>
            <div><dt class="text-slate-500">Trámite</dt><dd class="font-medium text-slate-900">{{ $solicitud->tramite ?: 'N/D' }}</dd></div>
            <div><dt class="text-slate-500">Estado</dt><dd class="font-semibold text-red-800">{{ $solicitud->estado }}</dd></div>
            <div><dt class="text-slate-500">Fecha de envío</dt><dd class="font-medium text-slate-900">{{ $solicitud->fecha }}</dd></div>
            <div><dt class="text-slate-500">Código CAFI</dt><dd class="font-medium text-slate-900">{{ $solicitud->codigo_sistema ?: 'Pendiente de asignación' }}</dd></div>
        </dl>
        <div class="mt-4 border-t border-slate-100 pt-4"><h3 class="text-sm font-semibold text-slate-700">Detalle declarado</h3><pre class="mt-2 whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ is_array($solicitud->detalle) ? json_encode($solicitud->detalle, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : ($solicitud->detalle ?: 'Sin detalle adicional.') }}</pre></div>
    </section>

    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Documentos del expediente</h2>
        @forelse($solicitud->respaldos as $documento)
            <div class="mb-3 grid gap-3 rounded-lg border border-slate-200 p-3 md:grid-cols-[1fr_auto]">
                <div><p class="font-medium text-slate-900">{{ $documento->nombre_original }}</p><p class="text-xs text-slate-500">{{ $documento->documentoRequerido?->nombre ?? 'Documento adjunto' }} · {{ number_format(($documento->tamano_bytes ?? 0)/1024,1) }} KB</p></div>
                <a href="{{ route('revision.documento',[$solicitud->id,$documento->id]) }}" target="_blank" class="rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-semibold text-slate-700 hover:border-red-700 hover:text-red-800">Ver documento</a>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay respaldos cargados en el expediente.</p>
        @endforelse
        @foreach($solicitud->documentosGenerados as $documento)
            <div class="mb-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">Documento generado · versión {{ $documento->version }} · {{ $documento->ruta_pdf ?: $documento->ruta_docx }}</div>
        @endforeach
    </section>

    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold text-slate-900">Requisitos y comprobación</h2>
        @if($requisitosProlog && $requisitosProlog['disponible'])
            <p class="mb-3 text-sm text-slate-600">Requisitos consultados en Prolog · versión {{ $requisitosProlog['kb_version'] }}</p>
            <ul class="mb-4 list-disc space-y-1 pl-5 text-sm text-slate-700">@foreach($requisitosProlog['items'] as $item)<li>{{ is_array($item) ? ($item['descripcion'] ?? $item['nombre'] ?? json_encode($item,JSON_UNESCAPED_UNICODE)) : $item }}</li>@endforeach</ul>
            <h3 class="mb-2 text-sm font-semibold text-slate-800">Documentos requeridos</h3>
            <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700">@foreach($requisitosProlog['documentos'] as $item)<li>{{ $item['nombre'] ?? $item['id'] }} @if($item['obligatorio'] ?? false)<span class="text-red-700">(obligatorio)</span>@endif</li>@endforeach</ul>
            @if($requisitosProlog['aviso'])<p class="mt-3 text-xs text-amber-800">{{ $requisitosProlog['aviso'] }}</p>@endif
        @else
            <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">{{ $requisitosProlog['error'] ?? 'El motor Prolog no está disponible en este momento.' }}</p>
        @endif
        @if($validacionProlog)<pre class="mt-4 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($validacionProlog,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>@endif
    </section>

    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Historial del expediente</h2>
        <ol class="space-y-3 border-l-2 border-red-100 pl-4">@forelse($lineaTiempo as $evento)<li class="relative"><span class="absolute -left-[22px] top-1 h-3 w-3 rounded-full bg-red-700"></span><p class="text-sm font-semibold text-slate-900">{{ $evento->estado_anterior ?: 'Inicio' }} → {{ $evento->estado_nuevo }}</p><p class="text-sm text-slate-600">{{ $evento->comentario ?: 'Sin observación' }}</p><p class="text-xs text-slate-500">{{ $evento->created_at?->format('d/m/Y H:i') }} · {{ $evento->user?->name ?? 'Sistema' }}</p></li>@empty<li class="text-sm text-slate-500">No hay movimientos registrados todavía.</li>@endforelse</ol>
    </section>

    @if(auth()->user()->trabajador && app(\App\Services\Academico\AutorizacionService::class)->tieneRol(auth()->user(),'asistente') && $solicitud->estadoSolicitud?->codigo === \App\Support\Estados::ENVIADA)
        <form action="{{ route('revision.guardar',$solicitud->id) }}" method="POST" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" onsubmit="return confirm('¿Registrar esta decisión y notificar al estudiante?')">@csrf
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Registrar revisión documental</h2>
            <div class="mb-4 grid gap-3 md:grid-cols-3 text-sm text-slate-700">
                <label class="flex items-center gap-2"><input type="checkbox" name="check_documento" value="1"> Documento legible</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="check_datos" value="1"> Datos coinciden</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="check_requisitos" value="1"> Requisitos completos</label>
            </div>
            <label class="mb-4 block text-sm font-medium text-slate-700">Observación <textarea name="observaciones" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-3" placeholder="Indica el motivo de observación o rechazo"></textarea></label>
            <div class="flex flex-wrap justify-end gap-2"><button name="accion" value="observar" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900">Marcar como observado</button><button name="accion" value="rechazar" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-800">Rechazar</button><button name="accion" value="aprobar" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Enviar al director</button></div>
        </form>
    @endif
@endsection

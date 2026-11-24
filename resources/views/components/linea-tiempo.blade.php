@props(['solicitud','internas'=>false])
<ol {{ $attributes }}>
@foreach(app(\App\Services\Flujo\EstadoSolicitudService::class)->lineaDeTiempo($solicitud,false) as $paso)
<li><time datetime="{{ $paso->created_at->toIso8601String() }}">{{ $paso->created_at->format('d/m/Y H:i') }}</time> · {{ app(\App\Services\Flujo\EstadoSolicitudService::class)->etiquetaParaEstudiante($paso->estado_nuevo) }}@if($paso->comentario)<p>{{ $paso->comentario }}</p>@endif</li>
@endforeach
</ol>

@extends('estudiante.layouts.marco')

@section('content')
@php
    $contenido = app(\App\Services\Catalogo\CatalogoTramites::class)->buscar($codigo);
    abort_unless($contenido, 404);
    $formulario = $contenido['formulario'];
    $requisitosMotor = app(\App\Services\Solicitudes\RequisitosSolicitud::class)->paraVista($codigo);
@endphp
@section('titulo', $contenido['nombre'])
<div class="portal-estudiante ficha-tramite">
@include('estudiante.partials.estilo-ficha-catalogo')
<main class="ft-contenedor">
    <nav class="ft-migas" aria-label="Ruta de navegación"><a href="{{ route('estudiante.catalogo') }}">Catálogo de trámites</a><span aria-hidden="true">/</span><span aria-current="page">{{ $contenido['nombre'] }}</span></nav>
    <header class="ft-presentacion">
        <span class="ft-icono ft-portada">@include('estudiante.partials.icono-ficha', ['icono' => 'documento'])</span>
        <div><span class="ft-etiqueta">Trámite académico</span><h1>{{ $contenido['nombre'] }}</h1><p>{{ $contenido['descripcion'] }}</p></div>
    </header>
    <div class="ft-distribucion">
        <div class="ft-principal">
            <section class="ft-tarjeta" aria-labelledby="requisitos">
                <div class="ft-titulo"><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'requisitos'])</span><h2 id="requisitos">Requisitos</h2></div>
                @include('estudiante.partials.requisitos-prolog')
                <p class="ft-nota">La facultad revisa los antecedentes y resuelve tu solicitud.</p>
            </section>
            <section class="ft-tarjeta" aria-labelledby="documentos">
                <div class="ft-titulo"><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'adjuntar'])</span><div><h2 id="documentos">Documentos de respaldo</h2><p>Revisa los documentos indicados para este trámite.</p></div></div>
                @include('estudiante.partials.documentos-prolog')
                <p class="ft-nota">Para enviar la solicitud, descarga el documento generado y adjunta su PDF firmado.</p>
            </section>
            @if ($requisitosMotor['aviso'])
                <aside class="ft-aviso" role="status"><span>@include('estudiante.partials.icono-ficha', ['icono' => 'informacion'])</span><div><h2>Ten en cuenta</h2><p>{{ $requisitosMotor['aviso'] }}</p></div></aside>
            @endif
            <details class="ft-tarjeta ft-detalle">
                <summary>Prepara tus datos <span aria-hidden="true">＋</span></summary>
                @if ($formulario['max_materias'] > 0)<p>Selecciona {{ $formulario['max_materias'] === 1 ? 'una materia' : 'las materias de tu solicitud' }} del catálogo académico.</p>@endif
                <ul>@foreach ($formulario['campos'] as $campo)<li>{{ $campo['etiqueta'] }} <span class="ft-condicion">{{ isset($campo['mostrar_si']) ? 'Según tu respuesta' : ($campo['obligatorio'] ? 'Obligatorio' : 'Opcional') }}</span></li>@endforeach</ul>
                <p>Completa tus datos personales y revisa que sean correctos antes de enviar.</p>
            </details>
        </div>
        <aside class="ft-lateral" aria-label="Cómo iniciar tu solicitud">
            <section class="ft-tarjeta" aria-labelledby="siguiente">
                <div class="ft-titulo"><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'requisitos'])</span><h2 id="siguiente">Tu siguiente paso</h2></div>
                <ol class="ft-pasos">
                    <li><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'persona'])</span><div><h3>Completa tus datos</h3><p>Ingresa tus datos y la información del trámite.</p></div></li>
                    <li><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'adjuntar'])</span><div><h3>Adjunta tus respaldos</h3><p>Carga los documentos que correspondan a tu solicitud.</p></div></li>
                    <li><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'firma'])</span><div><h3>Firma y sube el PDF</h3><p>Descarga el documento, fírmalo y adjúntalo para enviar.</p></div></li>
                </ol>
                <a class="ft-boton" href="{{ route('solicitudes.create', ['tramite' => $codigo]) }}">Iniciar solicitud <span aria-hidden="true">→</span></a>
                <a class="ft-enlace" href="{{ route('solicitudes.index') }}">Ver mis solicitudes</a>
                <p class="ft-borrador">@include('estudiante.partials.icono-ficha', ['icono' => 'documento']) Tus datos se guardan como borrador.</p>
            </section>
            <details class="ft-tarjeta ft-detalle ft-plazos" open>
                <summary><span class="ft-icono">@include('estudiante.partials.icono-ficha', ['icono' => 'calendario'])</span> Plazos y costos <span aria-hidden="true">＋</span></summary>
                <dl><dt>Plazo indicado</dt><dd>{{ $contenido['plazo_texto'] }}</dd><dt>Costos</dt><dd>{{ $contenido['costo_texto'] }}</dd></dl>
            </details>
        </aside>
    </div>
</main>
@include('estudiante.partials.chatbot-flotante')
</div>
@endsection

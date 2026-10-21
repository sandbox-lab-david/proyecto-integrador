@php
    $contenido = app(\App\Services\Catalogo\CatalogoTramites::class)->buscar($codigo);
    abort_unless($contenido, 404);
    $formulario = $contenido['formulario'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contenido['nombre'] }} | Portal del estudiante</title>
    @include('estudiante.partials.estilo-ficha')
    <style>main{max-width:1000px;margin:auto;padding:24px}h1{overflow-wrap:anywhere}.panel{background:white;border:1px solid #e0e4eb;border-radius:12px;padding:24px;margin:20px 0}li{margin:12px 0}.acciones{display:flex;gap:16px;flex-wrap:wrap}.boton{display:inline-block;background:#781c35;color:white;padding:12px 18px;border-radius:8px;text-decoration:none}a:focus-visible{outline:3px solid #2163a8;outline-offset:4px}@media(max-width:600px){.encabezado-contenido{flex-wrap:wrap;padding:16px}.marca{gap:10px}.nombre-portal{font-size:16px;padding-left:10px}.logo-uees{width:100px}main{padding:16px}.panel{padding:18px}}</style>
</head>
<body>
@include('estudiante.partials.encabezado-ficha')
<main>
    <a href="{{ route('estudiante.catalogo') }}">← Catálogo de trámites</a>
    <h1>{{ $contenido['nombre'] }}</h1>
    <p>{{ $contenido['descripcion'] }}</p>
    <section class="panel" aria-labelledby="requisitos"><h2 id="requisitos">Requisitos</h2>
        <ul>@foreach($contenido['requisitos'] as $requisito)<li>{{ $requisito }}</li>@endforeach</ul>
        <p>La facultad revisa los antecedentes y resuelve tu solicitud.</p>
    </section>
    <section class="panel" aria-labelledby="documentos"><h2 id="documentos">Documentos</h2>
        <ul>@foreach($contenido['documentos'] as $documento)<li><strong>{{ $documento['nombre'] }}</strong> · {{ ['obligatorio'=>'Obligatorio','opcional'=>'Opcional','condicional'=>'Según tu caso'][$documento['obligacion']] }}</li>@endforeach</ul>
        <p>Para enviar la solicitud, descarga el documento generado y adjunta su PDF firmado.</p>
    </section>
    <section class="panel" aria-labelledby="plazos"><h2 id="plazos">Plazos y costos</h2><p>{{ $contenido['plazo_texto'] }}</p><p>{{ $contenido['costo_texto'] }}</p></section>
    <section class="panel" aria-labelledby="datos"><h2 id="datos">Datos para completar el formulario</h2>
        @if($formulario['max_materias']>0)<p>Completa los datos de {{ $formulario['max_materias']===1 ? 'una materia' : 'las materias de tu solicitud' }}.</p>@endif
        <ul>@foreach($formulario['campos'] as $campo)<li>{{ $campo['etiqueta'] }} · {{ isset($campo['mostrar_si']) ? 'Según tu respuesta' : ($campo['obligatorio']?'Obligatorio':'Opcional') }}</li>@endforeach</ul>
    </section>
    <div class="acciones"><a class="boton" href="{{ route('solicitudes.create',['tramite'=>$codigo]) }}">Iniciar solicitud</a><a href="{{ route('chatbot.index',['tramite'=>$contenido['id']]) }}">Consultar en el chatbot</a><a href="{{ route('solicitudes.index') }}">Mis solicitudes</a></div>
</main>
@include('estudiante.partials.chatbot-flotante')
</body></html>

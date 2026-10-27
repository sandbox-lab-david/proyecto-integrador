<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agendas del Consejo UEES</title>
    <style>
        @page { size: letter; margin: 2cm 2.2cm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #171717; font: 11pt/1.4 Arial, Helvetica, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 2; padding: 14px; background: #f4f4f5; text-align: center; }
        .toolbar button { border: 0; border-radius: 7px; padding: 10px 18px; color: white; background: #b5122b; font-weight: 700; cursor: pointer; }
        .toolbar a { margin-left: 12px; color: #444; }
        main { max-width: 850px; margin: 28px auto; }
        .brand { display: block; width: 74px; height: auto; margin: 0 auto 12px; }
        h1 { margin: 0; text-align: center; font-size: 14pt; line-height: 1.35; text-transform: uppercase; }
        .document-range { margin: 4px 0 24px; text-align: center; font-weight: 700; }
        .agenda { margin-top: 30px; }
        .agenda + .agenda { break-before: page; }
        .agenda-header { margin-bottom: 18px; padding-bottom: 8px; border-bottom: 1px solid #9ca3af; text-align: center; }
        .agenda-header h2 { margin: 0; font-size: 12pt; text-transform: uppercase; }
        .agenda-number { margin: 3px 0; color: #6b1127; font-weight: 700; }
        .career { margin: 24px 0 7px; text-align: center; font-weight: 700; text-transform: uppercase; background: #e5e5e5; }
        .case { margin: 0 0 20px; break-inside: avoid; }
        .case-title { margin: 0 0 4px; font-weight: 700; }
        .case-title .index { display: inline-block; min-width: 25px; color: #173f70; }
        .case-subtitle { margin: 0 0 4px; font-weight: 700; }
        .detail { margin: 0 0 5px; text-align: justify; white-space: pre-line; }
        .resolution { margin-top: 6px; }
        .resolution-number { margin-left: 8px; color: #555; font-size: 9pt; }
        .no-cases { padding: 20px; text-align: center; color: #666; }
        @media print { .toolbar { display: none; } main { max-width: none; margin: 0; } .career { break-after: avoid; } .case { break-inside: avoid; } }
        @media screen { body { background: #e5e7eb; } main { padding: 48px 64px; background: white; box-shadow: 0 2px 14px #0002; } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Imprimir / Guardar como PDF</button><a href="{{ route('revision.resoluciones', array_filter($filtros, fn ($valor) => $valor !== '')) }}">Volver a Resoluciones</a></div>
    <main>
        <img class="brand" src="{{ asset('images/estudiante/Logo-UEES.gif') }}" alt="UEES">
        <h1>Agendas de Consejo Directivo de la Facultad de Ingeniería</h1>
        <p class="document-range">@if($filtros['desde'] || $filtros['hasta'])Rango de sesiones: {{ $filtros['desde'] ? \Illuminate\Support\Carbon::parse($filtros['desde'])->format('d/m/Y') : 'Sin fecha inicial' }} a {{ $filtros['hasta'] ? \Illuminate\Support\Carbon::parse($filtros['hasta'])->format('d/m/Y') : 'Sin fecha final' }}@else Todas las agendas registradas @endif</p>
        @php($criterios = array_filter(['Estudiante' => $filtros['nombre'], 'Código' => $filtros['codigo'], 'Carrera' => $filtros['carrera'], 'Trámite' => $filtros['tramite'], 'Estado' => $filtros['estado']]))
        @if($criterios)<p class="document-range">Filtros: @foreach($criterios as $etiqueta => $valor){{ $etiqueta }}: {{ $valor }}{{ !$loop->last ? ' · ' : '' }}@endforeach</p>@endif

        @forelse($agendasDocumentadas as $agenda)
            <article class="agenda">
                <header class="agenda-header">
                    <h2>Agenda de Consejo Directivo</h2>
                    <p class="agenda-number">{{ $agenda->numero }}</p>
                    <p>Sesión del {{ \Illuminate\Support\Carbon::parse($agenda->fecha_sesion)->locale('es')->translatedFormat('l d \\d\\e F \\d\\e Y') }} · Estado: {{ $agenda->estado }}</p>
                </header>
                @forelse($agenda->solicitudesPorCarrera as $carrera => $solicitudes)
                    <h3 class="career">{{ $carrera }}</h3>
                    @foreach($solicitudes as $index => $caso)
                        <section class="case">
                            <p class="case-title"><span class="index">{{ $index + 1 }}.</span> {{ mb_strtoupper($caso->tramite ?: 'SOLICITUD') }}</p>
                            <p class="case-subtitle">{{ mb_strtoupper($caso->estudiante ?: 'EXPEDIENTE '.$caso->codigo_solicitud) }} ({{ $caso->codigo_solicitud }})</p>
                            <p class="detail"><strong>Detalle:</strong> {{ $caso->motivo ?: 'Sin detalle registrado.' }}@if($caso->codigo_siac) Código estudiantil: {{ $caso->codigo_siac }}.@endif @if($caso->materia) Materia: {{ $caso->materia }}.@endif</p>
                            <p class="detail"><strong>Estado de la solicitud:</strong> {{ $caso->estado }}</p>
                            <p class="resolution"><strong>Resolución:</strong> {{ $caso->decision ?: 'Pendiente' }} _______________________________. @if($caso->numero_resolucion)<span class="resolution-number">{{ $caso->numero_resolucion }}</span>@endif</p>
                        </section>
                    @endforeach
                @empty
                    <p class="no-cases">Esta agenda no contiene solicitudes.</p>
                @endforelse
            </article>
        @empty
            <p class="no-cases">No hay agendas que coincidan con los filtros seleccionados.</p>
        @endforelse
    </main>
</body>
</html>

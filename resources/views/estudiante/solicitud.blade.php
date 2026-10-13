{{--
    Seguimiento de una solicitud guardada (RF-2.13). Usa el badge de estado (C68)
    y la línea de tiempo (C69) de G3 y sus modelos, que todavía no están en
    develop: el controlador responde 404 hasta entonces y esta vista no se ha ejecutado.

    Los dos van como componentes dinámicos porque Blade busca los componentes al
    compilar: con la etiqueta directa, «view:cache» falla mientras G3 no los publique.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud {{ $solicitud->codigo }} | Portal del estudiante</title>

    @include('estudiante.partials.estilo-ficha')
    @include('estudiante.partials.estilo-seguimiento')
</head>
<body>
    @include('estudiante.partials.encabezado-ficha')

    <main>
        <a class="volver" href="{{ route('solicitudes.index') }}">
            ← Volver a mis solicitudes
        </a>

        <div class="introduccion"><span class="categoria">Solicitud {{ $solicitud->codigo }}</span>

            <h1>{{ $solicitud->tipoTramite->nombre }}</h1>

            <div class="estado-solicitud">
                <x-dynamic-component component="estado-badge" :estado="$solicitud->estadoSolicitud" vista="estudiante" />

                <span class="dato">
                    {{ $solicitud->enviada_at
                        ? 'Enviada el '.$solicitud->enviada_at->format('d/m/Y')
                        : 'Creada el '.$solicitud->created_at->format('d/m/Y') }}
                </span>
            </div>
        </div>

        <div class="contenido">
            @php
                $resolucion = $solicitud->resolucion;
            @endphp

            @if ($resolucion !== null)
                @php
                    $decision = $resolucion->decision instanceof BackedEnum
                        ? $resolucion->decision->value
                        : $resolucion->decision;
                @endphp

                <section class="panel" aria-labelledby="titulo-resolucion">
                    <h2 id="titulo-resolucion">Resolución final</h2>

                    <dl class="datos-resolucion">
                        <div>
                            <dt>Decisión</dt>
                            <dd>{{ ['aprueba' => 'Aprobada', 'reprueba' => 'Reprobada'][$decision] ?? $decision }}</dd>
                        </div>

                        <div>
                            <dt>Número</dt>
                            <dd>{{ $resolucion->numero }}</dd>
                        </div>

                        <div>
                            <dt>Fecha</dt>
                            <dd>{{ \Illuminate\Support\Carbon::parse($resolucion->fecha)->format('d/m/Y') }}</dd>
                        </div>
                    </dl>

                    @if (filled($resolucion->observacion))
                        <p class="texto-observacion">{{ $resolucion->observacion }}</p>
                    @endif

                    @if (filled($resolucion->ruta_pdf))
                        <x-visor-documento
                            :ruta="$resolucion->ruta_pdf"
                            mime="application/pdf"
                            :nombre="'Resolución '.$resolucion->numero.'.pdf'"
                        />
                    @endif
                </section>
            @endif

            <section class="panel" aria-labelledby="titulo-observaciones">
                <h2 id="titulo-observaciones">Observaciones</h2>

                @if ($observaciones->isEmpty())
                    <p class="vacio">La facultad no ha dejado observaciones sobre esta solicitud.</p>
                @else
                    <ul class="lista-observaciones">
                        @foreach ($observaciones as $observacion)
                            <li>
                                <div class="estado-solicitud">
                                    @if ($observacion['estado'] !== null)
                                        <x-dynamic-component component="estado-badge" :estado="$observacion['estado']" vista="estudiante" />
                                    @endif

                                    <span class="dato">
                                        {{ \Illuminate\Support\Carbon::parse($observacion['fecha'])->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <p class="texto-observacion">{{ $observacion['texto'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="panel" aria-labelledby="titulo-linea-tiempo">
                <h2 id="titulo-linea-tiempo">Línea de tiempo</h2>

                <x-dynamic-component component="linea-tiempo" :solicitud="$solicitud" :internas="false" />
            </section>
        </div>
    </main>
</body>
</html>

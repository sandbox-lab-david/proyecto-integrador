{{--
    Seguimiento de una solicitud guardada (RF-2.13). Usa el badge de estado (C68)
    y la línea de tiempo (C69) publicados por G3. El controlador comprueba
    los permisos antes de renderizar; las observaciones internas no se muestran.
--}}
@extends('estudiante.layouts.marco')
@section('titulo', 'Seguimiento de solicitud')

@section('content')
<div class="portal-estudiante">
    @include('estudiante.partials.estilo-layout')

    @include('estudiante.partials.estilo-ficha')
    @include('estudiante.partials.estilo-seguimiento')

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
            @can('update', $solicitud)
                <section class="panel">
                    <h2>Tu solicitud necesita correcciones</h2>
                    <p>Revisa las observaciones de la facultad antes de editar los datos y actualizar los documentos.</p>
                    <a class="boton-fila" href="{{ route('solicitudes.edit', $solicitud) }}">Corregir solicitud →</a>
                </section>
            @endcan
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
                            <dd>{{ $resolucion->numero_resolucion }}</dd>
                        </div>

                        <div>
                            <dt>Fecha</dt>
                            <dd>{{ \Illuminate\Support\Carbon::parse($resolucion->fecha_resolucion)->format('d/m/Y') }}</dd>
                        </div>
                    </dl>

                    @if (filled($resolucion->observacion))
                        <p class="texto-observacion">{{ $resolucion->observacion }}</p>
                    @endif

                    @if (filled($resolucion->documento_pdf))
                        <x-visor-documento
                            :ruta="$resolucion->documento_pdf"
                            mime="application/pdf"
                            :nombre="'Resolución '.$resolucion->numero_resolucion.'.pdf'"
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
    @include('estudiante.partials.chatbot-flotante')
</div>
@endsection

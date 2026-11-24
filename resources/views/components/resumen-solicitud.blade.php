{{--
    Tarjeta con los datos, campos y materias que declaró el estudiante (contrato C57).

    <x-resumen-solicitud :solicitud="$solicitud" />
    <x-resumen-solicitud :solicitud="$solicitud" :encabezado="false" />

    Sin encabezado omite código, trámite, estado y fecha, para páginas que ya los muestran.
    class="resumen-solicitud--sin-marco" quita el borde y el relleno, para ponerla dentro de otro panel.
    La página que lo incluye ya debe haber autorizado a quien la ve (SolicitudPolicy::view):
    la tarjeta muestra la cédula, el correo y el celular del estudiante.
--}}
@props(['solicitud', 'encabezado' => true])

@inject('resumenes', 'App\Services\Solicitudes\ResumenSolicitud')

@php
    $resumen = $resumenes->de($solicitud);
    $nombreTramite = $solicitud->tipoTramite?->nombre ?? Str::ucfirst($resumen['nombre_tramite'] ?? 'trámite');
@endphp

<article {{ $attributes->class('resumen-solicitud') }}>
    @if ($encabezado)
        <header class="resumen-solicitud__encabezado">
            <div>
                <span class="resumen-solicitud__codigo">{{ $solicitud->codigo }}</span>
                <h3>{{ $nombreTramite }}</h3>
            </div>

            <div class="resumen-solicitud__estado">
                <x-estado-badge :estado="$solicitud->estadoSolicitud" />
                <span>
                    {{ $solicitud->enviada_at
                        ? 'Enviada el '.$solicitud->enviada_at->timezone('America/Guayaquil')->format('d/m/Y H:i')
                        : 'Borrador sin enviar' }}
                </span>
            </div>
        </header>
    @endif

    <section>
        <h4>Datos del estudiante</h4>

        @if ($resumen['personales'] === [])
            <p class="resumen-solicitud__vacio">La solicitud no tiene datos del estudiante.</p>
        @else
            <dl class="resumen-solicitud__datos">
                @foreach ($resumen['personales'] as $dato)
                    <div>
                        <dt>{{ $dato['etiqueta'] }}</dt>
                        <dd>{{ $dato['valor'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </section>

    <section>
        <h4>Datos del trámite</h4>

        @if ($resumen['campos'] === [])
            <p class="resumen-solicitud__vacio">Este trámite no tiene datos adicionales.</p>
        @else
            <dl class="resumen-solicitud__datos">
                @foreach ($resumen['campos'] as $dato)
                    <div @class(['resumen-solicitud__largo' => $dato['largo']])>
                        <dt>{{ $dato['etiqueta'] }}</dt>
                        <dd>{{ $dato['valor'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </section>

    @if ($resumen['materias'] !== [])
        <section>
            <h4>Materias ({{ count($resumen['materias']) }})</h4>

            <ol class="resumen-solicitud__materias">
                @foreach ($resumen['materias'] as $materia)
                    <li>
                        <strong>{{ $materia['titulo'] }}</strong>

                        @if ($materia['contexto'] !== '')
                            <span class="resumen-solicitud__contexto">{{ $materia['contexto'] }}</span>
                        @endif

                        @if ($materia['datos'] !== [])
                            <dl class="resumen-solicitud__datos">
                                @foreach ($materia['datos'] as $dato)
                                    <div @class(['resumen-solicitud__largo' => $dato['largo']])>
                                        <dt>{{ $dato['etiqueta'] }}</dt>
                                        <dd>{{ $dato['valor'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
</article>

@once
    <style>
        .resumen-solicitud {
            display: grid;
            gap: 20px;
            padding: 20px;
            border: 1px solid #e0e4eb;
            border-radius: 12px;
            background: #fff;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.5;
        }

        .resumen-solicitud--sin-marco { padding: 0; border: 0; background: none; }

        .resumen-solicitud h3,
        .resumen-solicitud h4 { margin: 0; color: #1f2937; }
        .resumen-solicitud h3 { font-size: 18px; }

        .resumen-solicitud h4 {
            margin-bottom: 10px;
            color: #781c35;
            font-size: 13px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .resumen-solicitud__encabezado {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e0e4eb;
        }

        .resumen-solicitud__codigo { color: #596579; font-size: 13px; font-weight: bold; }

        .resumen-solicitud__estado {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            color: #596579;
            font-size: 13px;
        }

        .resumen-solicitud__datos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px 20px;
            margin: 0;
        }

        .resumen-solicitud__datos dt { color: #596579; font-size: 12px; }
        .resumen-solicitud__datos dd { margin: 2px 0 0; font-weight: 600; overflow-wrap: anywhere; }
        .resumen-solicitud__largo { grid-column: 1 / -1; }
        .resumen-solicitud__largo dd { font-weight: normal; white-space: pre-line; }

        .resumen-solicitud__materias {
            display: grid;
            gap: 12px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .resumen-solicitud__materias li {
            display: grid;
            gap: 8px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f5f6fa;
        }

        .resumen-solicitud__contexto { color: #596579; font-size: 13px; }
        .resumen-solicitud__vacio { margin: 0; color: #596579; }
    </style>
@endonce

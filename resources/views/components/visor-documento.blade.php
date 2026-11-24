{{--
    Muestra un PDF o una imagen del storage privado, con su enlace de descarga (contrato C55).

    <x-visor-documento :ruta="$respaldo->ruta" :mime="$respaldo->mime" :nombre="$respaldo->nombre_original" />

    La página que lo incluye ya debe haber comprobado que quien la ve puede abrir el
    archivo: los enlaces que genera sirven durante una hora sin volver a preguntar.
--}}
@props(['ruta', 'mime', 'nombre' => null])

@inject('archivos', 'App\Services\Archivos\ArchivoService')

@php
    $nombre ??= basename($ruta);
    $urlVer = $archivos->urlTemporal($ruta, $nombre);
@endphp

<figure {{ $attributes->class('visor-documento') }}>
    @if (in_array($mime, ['image/jpeg', 'image/png'], true))
        <img src="{{ $urlVer }}" alt="{{ $nombre }}">
    @elseif ($mime === 'application/pdf')
        <iframe src="{{ $urlVer }}" title="{{ $nombre }}"></iframe>
    @else
        <p class="visor-documento__aviso">
            Este tipo de archivo no se puede mostrar aquí. Descárgalo para abrirlo.
        </p>
    @endif

    <figcaption>
        <span class="visor-documento__nombre">{{ $nombre }}</span>

        <span class="visor-documento__enlaces">
            @if (in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true))
                <a href="{{ $urlVer }}" target="_blank" rel="noopener">Abrir en otra pestaña</a>
            @endif

            <a href="{{ $archivos->urlTemporal($ruta, $nombre, descargar: true) }}">Descargar</a>
        </span>
    </figcaption>
</figure>

@once
    <style>
        .visor-documento { margin: 0; }

        .visor-documento img,
        .visor-documento iframe {
            display: block;
            width: 100%;
            border: 1px solid #e0e4eb;
            border-radius: 10px;
            background: #f5f6fa;
        }

        .visor-documento img { height: auto; max-height: 70vh; object-fit: contain; }
        .visor-documento iframe { height: 70vh; }

        .visor-documento__aviso {
            margin: 0;
            padding: 20px;
            border-radius: 10px;
            background: #f5f6fa;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .visor-documento figcaption {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px 16px;
            margin-top: 12px;
            font-size: 14px;
        }

        .visor-documento__nombre { font-weight: bold; overflow-wrap: anywhere; }
        .visor-documento__enlaces { display: flex; flex-wrap: wrap; gap: 8px 16px; }
        .visor-documento a { color: #781c35; }
    </style>
@endonce

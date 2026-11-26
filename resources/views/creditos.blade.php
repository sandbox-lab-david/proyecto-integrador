<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos · Sistema de Solicitudes Estudiantiles · UEES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .creditos h3 { margin: 2.5rem 0 1rem; font-size: 1.375rem; font-weight: 700; color: #25272c; }
        .creditos h3:first-child { margin-top: 0; }
        .creditos h4 { margin: 1.5rem 0 0.5rem; font-size: 1.0625rem; font-weight: 600; color: #25272c; }
        .creditos p { margin: 0.75rem 0; line-height: 1.6; }
        .creditos ul, .creditos ol { margin: 0.75rem 0; padding-left: 1.5rem; line-height: 1.6; }
        .creditos ul { list-style: disc; }
        .creditos ol { list-style: decimal; }
        .creditos a { color: #862633; }
        .creditos hr { margin: 2.5rem 0; border: 0; border-top: 1px solid #dcdeda; }
        .creditos code { padding: 0.1rem 0.3rem; border-radius: 0.25rem; background: #eef0f3; font-size: 0.875em; }
        .creditos table { width: 100%; margin: 1rem 0; border-collapse: collapse; }
        .creditos th, .creditos td { padding: 0.5rem; border: 1px solid #eeefec; vertical-align: top; }
        .creditos td[align="center"] { text-align: center; }
        .creditos img { display: inline-block; max-width: 100%; }
        .creditos td img[width="100"] { width: 88px; height: 88px; border-radius: 9999px; }
        .creditos .tabla-desplazable { overflow-x: auto; }
    </style>
</head>
<body class="ui-pagina">
    <x-layouts.barra-superior modulo="Sistema de Solicitudes Estudiantiles" />

    <main class="px-normal py-grande">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mb-amplio flex flex-wrap items-center justify-between gap-medio">
                <h1 class="ui-titulo">Créditos</h1>
                <a class="ui-btn ui-btn-quieto ui-btn-chico" href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">Volver</a>
            </div>

            <div class="ui-tarjeta creditos tabla-desplazable">
                {!! $creditos !!}
            </div>
        </div>
    </main>

    <x-pie-creditos />
</body>
</html>

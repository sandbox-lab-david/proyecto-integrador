@php $titulo = $attributes->get('titulo'); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ? $titulo.' · ' : '' }}Sistema de Solicitudes Estudiantiles · UEES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ui-pagina">
    <x-layouts.barra-superior modulo="Sistema de Solicitudes Estudiantiles" />

    <main class="px-normal py-grande">
        <div class="mx-auto w-full max-w-md">
            <div class="ui-tarjeta">
                {{ $slot }}
            </div>
        </div>
    </main>
</body>
</html>

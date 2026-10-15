@props(['titulo' => 'Asistente de trámites'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · UEES</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('assets/chatbot/chatbot.css') }}">
</head>
<body class="cb cb-cuerpo">
<div class="cb-pagina">
    <header class="cb-top">
        <img src="{{ asset('assets/chatbot/img/facultad-ingenieria-uees.png') }}" alt="UEES Facultad de Ingeniería">
        <span class="cb-top-titulo">{{ $titulo }}</span>
        <a class="cb-top-enlace" href="{{ url('/') }}">Inicio</a>
    </header>
    <main class="cb-pagina-main">
        {{ $slot }}
    </main>
</div>
</body>
</html>

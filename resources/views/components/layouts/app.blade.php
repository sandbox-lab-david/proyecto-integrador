{{-- C37 --}}
@props([
    'titulo' => null,
    'modulo' => 'Sistema de Solicitudes Estudiantiles',
    'menu' => [],
])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ? $titulo.' · ' : '' }}{{ $modulo }} · UEES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ui-pagina">
    <x-layouts.barra-superior :modulo="$modulo" />

    <div class="flex flex-col md:flex-row md:min-h-[calc(100dvh-68px)]">
        <x-layouts.menu-lateral :menu="$menu" :modulo="$modulo" />

        <main class="flex-1 min-w-0">
            <div class="ui-contenedor">
                @if (session('exito') || session('success'))
                    <div class="ui-alerta ui-alerta-exito mb-amplio" role="status">{{ session('exito') ?? session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="ui-alerta ui-alerta-error mb-amplio" role="alert">{{ session('error') }}</div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div class="ui-alerta ui-alerta-error mb-amplio" role="alert">
                        <strong>No se pudo guardar</strong>
                        Revisa los campos marcados en el formulario.
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>

@extends('layouts.app')

@section('titulo', 'Inicio')

@section('content')
    <p class="ui-antetitulo">Grupo 1 · Módulo de administración</p>
    <h1 class="ui-titulo">Administración académica</h1>
    <p class="ui-texto-gris mt-chico">Catálogos que usan los demás módulos: estructura institucional, periodos, materias, paralelos e importaciones del SIAC.</p>

    <dl class="ui-datos mt-amplio">
        @foreach ($conteos as $nombre => $total)
            <div class="ui-dato">
                <dt>{{ $nombre }}</dt>
                <dd>{{ $total }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="ui-alerta {{ $periodoActivo ? 'ui-alerta-exito' : 'ui-alerta-aviso' }} mt-amplio">
        @if ($periodoActivo)
            <strong>Periodo activo</strong>
            {{ $periodoActivo->nombre }} ({{ \Illuminate\Support\Carbon::parse($periodoActivo->fecha_inicio)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($periodoActivo->fecha_fin)->format('d/m/Y') }})
        @else
            <strong>Sin periodo activo</strong>
            Crea o activa un periodo para que los demás módulos puedan asignarlo a las solicitudes.
        @endif
    </div>

    <h2 class="ui-subtitulo mt-grande mb-medio">Secciones</h2>
    <div class="ui-tarjetas">
        <a class="ui-tarjeta" href="{{ route('administracion.periodos') }}">
            <p class="ui-etiqueta ui-etiqueta-vino">Estructura académica</p>
            <h3 class="ui-subtitulo">Periodos académicos</h3>
            <p class="ui-texto-gris">Crea periodos y define cuál está activo.</p>
        </a>
        <a class="ui-tarjeta" href="{{ route('administracion.materias') }}">
            <p class="ui-etiqueta ui-etiqueta-vino">Estructura académica</p>
            <h3 class="ui-subtitulo">Materias</h3>
            <p class="ui-texto-gris">Registra materias con su código, créditos y categoría.</p>
        </a>
        <a class="ui-tarjeta" href="{{ route('administracion.paralelos') }}">
            <p class="ui-etiqueta ui-etiqueta-vino">Estructura académica</p>
            <h3 class="ui-subtitulo">Paralelos y docentes</h3>
            <p class="ui-texto-gris">Abre paralelos de una materia en un periodo y asigna su docente.</p>
        </a>
        <a class="ui-tarjeta" href="{{ route('cargas.index') }}">
            <p class="ui-etiqueta">Datos</p>
            <h3 class="ui-subtitulo">Importaciones SIAC</h3>
            <p class="ui-texto-gris">Sube archivos del SIAC, revisa la vista previa y consulta el historial.</p>
        </a>
    </div>
@endsection

@extends('layouts.app')

@section('titulo', 'Paralelos y docentes')

@section('content')
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Paralelos y docentes</h1>
    <p class="ui-texto-gris mt-chico">Cada paralelo es una materia ofertada en un periodo, con su docente. El portal del estudiante los usa para completar paralelo y docente automáticamente.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">Nuevo paralelo</h2>
            @if ($materias->isEmpty() || $periodos->isEmpty())
                <div class="ui-alerta ui-alerta-aviso">
                    <strong>Faltan datos</strong>
                    Primero registra al menos una <a class="ui-enlace" href="{{ route('administracion.materias') }}">materia</a> y un <a class="ui-enlace" href="{{ route('administracion.periodos') }}">periodo</a>.
                </div>
            @endif
            <form method="POST" action="{{ route('administracion.paralelos.store') }}" class="ui-form">
                @csrf
                <div class="ui-campo @error('materia_id') ui-campo-error @enderror">
                    <label for="materia_id">Materia</label>
                    <select id="materia_id" name="materia_id" class="ui-control">
                        <option value="">Selecciona una materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" @selected((string) old('materia_id') === (string) $materia->id)>{{ $materia->codigo }} · {{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                    @error('materia_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('periodo_id') ui-campo-error @enderror">
                    <label for="periodo_id">Periodo</label>
                    <select id="periodo_id" name="periodo_id" class="ui-control">
                        <option value="">Selecciona un periodo</option>
                        @foreach ($periodos as $periodo)
                            <option value="{{ $periodo->id }}" @selected((string) old('periodo_id', $periodos->firstWhere('activo', true)?->id) === (string) $periodo->id)>{{ $periodo->nombre }}{{ $periodo->activo ? ' (activo)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('periodo_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('profesor_id') ui-campo-error @enderror">
                    <label for="profesor_id">Docente <span class="ui-opcional">(opcional)</span></label>
                    <select id="profesor_id" name="profesor_id" class="ui-control">
                        <option value="">Sin asignar</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" @selected((string) old('profesor_id') === (string) $docente->id)>{{ $docente->apellidos }}, {{ $docente->nombres }}</option>
                        @endforeach
                    </select>
                    @error('profesor_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-medio">
                    <div class="ui-campo @error('paralelo') ui-campo-error @enderror">
                        <label for="paralelo">Paralelo</label>
                        <input id="paralelo" name="paralelo" class="ui-control" value="{{ old('paralelo') }}" placeholder="A1" maxlength="20">
                        @error('paralelo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-campo @error('cupo') ui-campo-error @enderror">
                        <label for="cupo">Cupo <span class="ui-opcional">(opcional)</span></label>
                        <input id="cupo" name="cupo" type="number" min="1" max="200" class="ui-control" value="{{ old('cupo') }}">
                        @error('cupo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario" @disabled($materias->isEmpty() || $periodos->isEmpty())>Guardar paralelo</button>
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Paralelos registrados</h2>
            @if ($cursos->isEmpty())
                <div class="ui-vacio">Todavía no hay paralelos registrados.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Paralelo</th>
                                <th>Periodo</th>
                                <th>Docente</th>
                                <th>Cupo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursos as $curso)
                                <tr>
                                    <td>
                                        <span class="font-semibold">{{ $curso->materia->codigo ?? '—' }}</span>
                                        <span class="block text-chico text-gris">{{ $curso->materia->nombre ?? '' }}</span>
                                    </td>
                                    <td><span class="ui-badge ui-badge-vino">{{ $curso->paralelo }}</span></td>
                                    <td>{{ $curso->periodo->nombre ?? '—' }}</td>
                                    <td>{{ $curso->profesor ? $curso->profesor->nombres.' '.$curso->profesor->apellidos : 'Sin asignar' }}</td>
                                    <td>{{ $curso->cupo ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection

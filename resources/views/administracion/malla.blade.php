<x-layouts.app titulo="Malla {{ $malla->version }}" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo"><a class="ui-enlace" href="{{ route('administracion.mallas') }}">Mallas</a> / {{ $malla->carrera->nombre }}</p>
    <h1 class="ui-titulo">
        Malla {{ $malla->version }}
        @if ($malla->vigente)
            <span class="ui-badge ui-badge-exito align-middle">Vigente</span>
        @endif
    </h1>
    <p class="ui-texto-gris mt-chico">Estas son las materias que el estudiante de {{ $malla->carrera->nombre }} puede elegir en sus solicitudes, ordenadas por nivel.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">Agregar materia</h2>
            @if ($disponibles->isEmpty())
                <div class="ui-alerta ui-alerta-aviso">
                    No quedan materias activas por agregar. Registra más en <a class="ui-enlace" href="{{ route('administracion.materias') }}">Materias</a>.
                </div>
            @else
                <form method="POST" action="{{ route('administracion.mallas.materias.store', $malla) }}" class="ui-form">
                    @csrf
                    <div class="ui-campo @error('materia_id') ui-campo-error @enderror">
                        <label for="materia_id">Materia</label>
                        <select id="materia_id" name="materia_id" class="ui-control">
                            <option value="">Selecciona una materia</option>
                            @foreach ($disponibles as $materia)
                                <option value="{{ $materia->id }}" @selected((string) old('materia_id') === (string) $materia->id)>{{ $materia->codigo }} · {{ $materia->nombre }}</option>
                            @endforeach
                        </select>
                        @error('materia_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-campo @error('nivel') ui-campo-error @enderror">
                        <label for="nivel">Nivel</label>
                        <input id="nivel" name="nivel" type="number" min="1" max="20" class="ui-control" value="{{ old('nivel', 1) }}">
                        @error('nivel')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-acciones">
                        <button type="submit" class="ui-btn ui-btn-primario">Agregar a la malla</button>
                    </div>
                </form>
            @endif
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Materias de la malla</h2>
            @if ($niveles->isEmpty())
                <div class="ui-vacio">Esta malla todavía no tiene materias.</div>
            @else
                @foreach ($niveles as $nivel => $materias)
                    <h3 class="font-semibold mt-medio mb-chico">Nivel {{ $nivel }}</h3>
                    <div class="ui-tabla-caja">
                        <table class="ui-tabla">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Materia</th>
                                    <th>Créditos</th>
                                    <th><span class="ui-sr-oculto">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($materias as $materia)
                                    <tr>
                                        <td class="font-semibold">{{ $materia->codigo }}</td>
                                        <td>
                                            {{ $materia->nombre }}
                                            @unless ($materia->activo)
                                                <span class="ui-badge">Inactiva</span>
                                            @endunless
                                        </td>
                                        <td>{{ $materia->creditos }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('administracion.mallas.materias.destroy', [$malla, $materia]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </section>
    </div>
</x-layouts.app>

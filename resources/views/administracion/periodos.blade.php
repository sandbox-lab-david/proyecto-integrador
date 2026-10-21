<x-layouts.app titulo="Periodos académicos" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Periodos académicos</h1>
    <p class="ui-texto-gris mt-chico">Solo puede haber un periodo activo a la vez: al marcar uno nuevo como activo, el anterior se desactiva.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">Nuevo periodo</h2>
            <form method="POST" action="{{ route('administracion.periodos.store') }}" class="ui-form">
                @csrf
                <div class="ui-campo @error('codigo') ui-campo-error @enderror">
                    <label for="codigo">Código</label>
                    <input id="codigo" name="codigo" class="ui-control" value="{{ old('codigo') }}" placeholder="ORD-2-2026" maxlength="30">
                    @error('codigo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('nombre') ui-campo-error @enderror">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" class="ui-control" value="{{ old('nombre') }}" placeholder="Ordinario II 2026">
                    @error('nombre')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('tipo') ui-campo-error @enderror">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="ui-control">
                        @foreach (['ordinario' => 'Ordinario', 'extraordinario' => 'Extraordinario', 'intensivo' => 'Intensivo'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('tipo', 'ordinario') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                    @error('tipo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-medio">
                    <div class="ui-campo @error('fecha_inicio') ui-campo-error @enderror">
                        <label for="fecha_inicio">Inicio</label>
                        <input id="fecha_inicio" name="fecha_inicio" type="date" class="ui-control" value="{{ old('fecha_inicio') }}">
                        @error('fecha_inicio')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-campo @error('fecha_fin') ui-campo-error @enderror">
                        <label for="fecha_fin">Fin</label>
                        <input id="fecha_fin" name="fecha_fin" type="date" class="ui-control" value="{{ old('fecha_fin') }}">
                        @error('fecha_fin')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="ui-campo @error('semana_primer_parcial') ui-campo-error @enderror">
                    <label for="semana_primer_parcial">Semana del primer parcial</label>
                    <input id="semana_primer_parcial" name="semana_primer_parcial" type="number" min="1" max="20" class="ui-control" value="{{ old('semana_primer_parcial', 8) }}">
                    <p class="ui-ayuda">Se usa para calcular el costo del retiro de materia.</p>
                    @error('semana_primer_parcial')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <label class="ui-casilla">
                    <input type="checkbox" name="activo" value="1" @checked(old('activo'))>
                    <span>Marcar como periodo activo</span>
                </label>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">Guardar periodo</button>
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Periodos registrados</h2>
            @if ($periodos->isEmpty())
                <div class="ui-vacio">Todavía no hay periodos registrados.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fechas</th>
                                <th>1.er parcial</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($periodos as $periodo)
                                <tr>
                                    <td class="font-semibold">{{ $periodo->codigo }}</td>
                                    <td>{{ $periodo->nombre }}</td>
                                    <td>{{ ucfirst($periodo->tipo) }}</td>
                                    <td class="whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}</td>
                                    <td>Semana {{ $periodo->semana_primer_parcial }}</td>
                                    <td>
                                        @if ($periodo->activo)
                                            <span class="ui-badge ui-badge-exito">Activo</span>
                                        @else
                                            <span class="ui-badge">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>

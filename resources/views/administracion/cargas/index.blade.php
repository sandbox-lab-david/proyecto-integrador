<x-layouts.app titulo="Importaciones SIAC" modulo="Administración académica" :menu="config('menu_administracion')">
    <div class="flex flex-wrap items-end justify-between gap-normal">
        <div>
            <p class="ui-antetitulo">Datos</p>
            <h1 class="ui-titulo">Importaciones SIAC</h1>
            <p class="ui-texto-gris mt-chico">Carga los archivos exportados del SIAC. Antes de guardar verás una vista previa: qué filas son nuevas, cuáles actualizan datos que ya existen y cuáles tienen errores.</p>
        </div>
        <a href="{{ route('cargas.historial') }}" class="ui-btn ui-btn-secundario">Ver historial</a>
    </div>

    @if ($pendientes->isNotEmpty())
        <div class="ui-alerta ui-alerta-aviso mt-amplio">
            <strong>Tienes {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'archivo sin confirmar' : 'archivos sin confirmar' }}</strong>
            @foreach ($pendientes as $pendiente)
                <a class="ui-enlace block" href="{{ route('cargas.vista-previa', $pendiente) }}">{{ $pendiente->nombreArchivo() }} ({{ $pendiente->created_at->format('d/m/Y H:i') }})</a>
            @endforeach
        </div>
    @endif

    <div class="grid gap-amplio mt-amplio lg:grid-cols-2 items-start">
        <section class="ui-tarjeta">
            <p class="ui-etiqueta ui-etiqueta-vino">Paso 1</p>
            <h2 class="ui-subtitulo">Descarga la plantilla</h2>
            <p class="ui-texto-gris">Cada tipo tiene su plantilla con los encabezados esperados. Las columnas con * son obligatorias. Importa en este orden para que las referencias existan: carreras, materias, mallas, docentes, paralelos y estudiantes.</p>
            <div class="ui-tabla-caja mt-medio">
                <table class="ui-tabla">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Columnas</th>
                            <th><span class="ui-sr-oculto">Descargas</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($importadores as $entidad => $importador)
                            <tr>
                                <td class="font-semibold">{{ $importador->titulo() }}</td>
                                <td class="text-chico">
                                    @foreach ($importador->columnas() as $columna => $obligatoria)
                                        <code>{{ $columna }}{{ $obligatoria ? '*' : '' }}</code>@if (! $loop->last), @endif
                                    @endforeach
                                </td>
                                <td>
                                    <div class="flex flex-col items-end gap-chico">
                                        <a href="{{ route('cargas.plantilla', $entidad) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Plantilla</a>
                                        <a href="{{ route('cargas.ejemplo', $entidad) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Ejemplo</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-tarjeta">
            <p class="ui-etiqueta ui-etiqueta-vino">Paso 2</p>
            <h2 class="ui-subtitulo">Sube el archivo</h2>
            <form action="{{ route('cargas.preview') }}" method="POST" enctype="multipart/form-data" class="ui-form">
                @csrf
                <div class="ui-campo @error('entidad') ui-campo-error @enderror">
                    <label for="entidad">¿Qué vas a importar?</label>
                    <select name="entidad" id="entidad" class="ui-control">
                        <option value="">Selecciona el tipo de información</option>
                        @foreach ($importadores as $entidad => $importador)
                            <option value="{{ $entidad }}" @selected(old('entidad') === $entidad)>{{ $importador->titulo() }}</option>
                        @endforeach
                    </select>
                    @error('entidad')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('archivo') ui-campo-error @enderror">
                    <label for="archivo">Archivo</label>
                    <label class="ui-archivo">
                        <input type="file" name="archivo" id="archivo" accept=".xlsx,.xls,.csv">
                        <strong>Elige un archivo</strong>
                        <span class="ui-ayuda">.xlsx, .xls o .csv, hasta 10 MB</span>
                    </label>
                    @error('archivo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <p class="ui-ayuda">Todavía no se guarda nada: primero verás la vista previa. Los archivos de más de {{ \App\Services\Academico\ImportacionService::LIMITE_INMEDIATO }} filas se procesan en segundo plano.</p>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">Ver vista previa</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>

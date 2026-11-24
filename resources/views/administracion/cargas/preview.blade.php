<x-layouts.app titulo="Vista previa de la importación" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo"><a class="ui-enlace" href="{{ route('cargas.index') }}">Importaciones SIAC</a> · Paso 3</p>
    <h1 class="ui-titulo">Vista previa: {{ $importador->titulo() }}</h1>
    <p class="ui-texto-gris mt-chico">Archivo <strong>{{ $importacion->nombreArchivo() }}</strong>. Todavía no se ha guardado nada.</p>

    @if ($analisis['ilegible'])
        <div class="ui-alerta ui-alerta-error mt-amplio">
            <strong>No se pudo leer el archivo</strong>
            Revisa que sea un .xlsx, .xls o .csv válido y que no esté protegido con contraseña.
        </div>
    @elseif ($analisis['faltantes'])
        <div class="ui-alerta ui-alerta-error mt-amplio">
            <strong>Faltan columnas obligatorias</strong>
            El archivo no tiene: <code>{{ implode(', ', $analisis['faltantes']) }}</code>. Usa la plantilla de {{ mb_strtolower($importador->titulo()) }}.
        </div>
    @elseif ($analisis['resumen']['total'] === 0)
        <div class="ui-alerta ui-alerta-aviso mt-amplio">El archivo no tiene filas con datos.</div>
    @else
        <dl class="ui-datos mt-amplio">
            <div class="ui-dato"><dt>Nuevas</dt><dd>{{ $analisis['resumen']['nuevas'] }}</dd></div>
            <div class="ui-dato"><dt>Se actualizarán</dt><dd>{{ $analisis['resumen']['actualiza'] }}</dd></div>
            <div class="ui-dato"><dt>Con error (no se guardan)</dt><dd>{{ $analisis['resumen']['errores'] }}</dd></div>
        </dl>

        <h2 class="ui-subtitulo mt-amplio mb-medio">Filas del archivo</h2>
        @if ($analisis['resumen']['total'] > $filas->count())
            <p class="ui-ayuda mb-medio">Se muestran {{ $filas->count() }} de {{ $analisis['resumen']['total'] }} filas, primero las que tienen error.</p>
        @endif
        <div class="ui-tabla-caja">
            <table class="ui-tabla">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Resultado</th>
                        <th>Datos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $fila)
                        <tr>
                            <td class="font-semibold">{{ $fila['fila'] }}</td>
                            <td class="whitespace-nowrap">
                                @if ($fila['estado'] === 'error')
                                    <span class="ui-badge ui-badge-peligro">Error</span>
                                @elseif ($fila['estado'] === 'actualiza')
                                    <span class="ui-badge ui-badge-info">Se actualiza</span>
                                @else
                                    <span class="ui-badge ui-badge-exito">Nueva</span>
                                @endif
                            </td>
                            <td class="text-chico">
                                {{ collect($fila['datos'])->filter(fn ($v) => $v !== null)->map(fn ($v, $k) => "$k: $v")->join(' · ') }}
                                @foreach ($fila['motivos'] as $motivo)
                                    <span class="block ui-error-campo">{{ $motivo }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="ui-acciones mt-amplio">
        @if (! $analisis['ilegible'] && ! $analisis['faltantes'] && $analisis['resumen']['nuevas'] + $analisis['resumen']['actualiza'] > 0)
            <form method="POST" action="{{ route('cargas.procesar', $importacion) }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-primario">Confirmar e importar {{ $analisis['resumen']['nuevas'] + $analisis['resumen']['actualiza'] }} filas</button>
            </form>
        @endif
        <form method="POST" action="{{ route('cargas.cancelar', $importacion) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="ui-btn ui-btn-quieto">Cancelar</button>
        </form>
    </div>
</x-layouts.app>

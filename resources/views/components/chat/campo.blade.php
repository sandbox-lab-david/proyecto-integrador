@props(['tipo' => 'texto'])

<div class="cb-campo" data-cb="campo" data-tipo="{{ $tipo }}">
    @if ($tipo === 'booleano')
        <fieldset>
            <legend data-cb="campo-etiqueta"></legend>
            <div class="cb-segmentado">
                <label><input type="radio" value="1" data-cb="campo-control"><span>Sí</span></label>
                <label><input type="radio" value="0" data-cb="campo-control"><span>No</span></label>
            </div>
        </fieldset>
    @else
        <label data-cb="campo-etiqueta"></label>
        @if ($tipo === 'seleccion')
            <select class="cb-control" data-cb="campo-control">
                <option value="">Elige una opción</option>
            </select>
        @elseif ($tipo === 'numero')
            <input class="cb-control" type="number" inputmode="decimal" step="any" data-cb="campo-control">
        @elseif ($tipo === 'fecha')
            <input class="cb-control" type="date" data-cb="campo-control">
        @else
            <input class="cb-control" type="text" data-cb="campo-control">
        @endif
    @endif
    <span class="cb-ayuda" data-cb="campo-ayuda" hidden></span>
    <span class="cb-err" data-cb="campo-error" hidden></span>
</div>

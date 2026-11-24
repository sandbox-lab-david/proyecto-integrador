@switch ($estado)
    @case('completada')
        <span class="ui-badge ui-badge-exito">Completada</span>
        @break
    @case('procesando')
        <span class="ui-badge ui-badge-info">Procesando</span>
        @break
    @case('fallida')
        <span class="ui-badge ui-badge-peligro">Fallida</span>
        @break
    @default
        <span class="ui-badge">Sin confirmar</span>
@endswitch

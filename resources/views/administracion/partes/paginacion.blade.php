@if ($paginator->hasPages())
    <nav class="ui-paginacion mt-medio" aria-label="Páginas">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true">Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span aria-disabled="true">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $pagina => $url)
                    @if ($pagina == $paginator->currentPage())
                        <span aria-current="page">{{ $pagina }}</span>
                    @else
                        <a href="{{ $url }}">{{ $pagina }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
        @else
            <span aria-disabled="true">Siguiente</span>
        @endif
    </nav>
@endif

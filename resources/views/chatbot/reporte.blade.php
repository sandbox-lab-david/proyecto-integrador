@php
    $preguntas = collect($preguntas ?? []);
@endphp

<x-chat.pagina titulo="Preguntas no resueltas">
    <div class="cb-reporte">
        <div class="cb-reporte-cab">
            <p class="cb-eyebrow">Chatbot · Reporte</p>
            <h1>Preguntas no resueltas</h1>
            <p>Preguntas que el asistente no supo responder. Sirven para completar la base de conocimiento.</p>
        </div>

        @if ($preguntas->isEmpty())
            <div class="cb-vacio">
                <strong>No hay preguntas sin resolver</strong>
                <p>Cuando el asistente no entienda una pregunta, aparecerá aquí.</p>
            </div>
        @else
            <p class="cb-reporte-total">{{ $preguntas->count() }} {{ $preguntas->count() === 1 ? 'pregunta' : 'preguntas' }}</p>
            <div class="cb-tabla-caja">
                <table class="cb-tabla">
                    <thead>
                        <tr>
                            <th scope="col">Pregunta</th>
                            <th scope="col">Trámite detectado</th>
                            <th scope="col">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preguntas as $pregunta)
                            <tr>
                                <td>{{ data_get($pregunta, 'texto') }}</td>
                                <td>{{ data_get($pregunta, 'tramite') ?? 'Ninguno' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse(data_get($pregunta, 'fecha'))->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-chat.pagina>

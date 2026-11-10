@if (!$requisitosMotor['disponible'])
    <p>No se pudieron consultar los documentos de este trámite. Vuelve a intentarlo más tarde.</p>
@else
    @if (empty($requisitosMotor['documentos']))
        <p>Este trámite no pide documentos de respaldo.</p>
    @else
        <ul class="ft-documentos">
        @foreach ($requisitosMotor['documentos'] as $documentoMotor)
            <li class="ft-documento">@include('estudiante.partials.icono-ficha', ['icono' => 'documento'])
                <div><strong>{{ $documentoMotor['nombre'] }}</strong>
                    <span class="ft-condicion">{{ ['obligatorio' => 'Obligatorio', 'opcional' => 'Opcional', 'condicional' => 'Según tu caso'][$documentoMotor['obligacion'] ?? ($documentoMotor['obligatorio'] ? 'obligatorio' : 'opcional')] }}</span>
                    @if ($documentoMotor['descripcion'] ?? '')<p>{{ $documentoMotor['descripcion'] }}</p>@endif
                    @if ($documentoMotor['formato'] ?? '')<span class="ft-formato">Formato: {{ $documentoMotor['formato'] }}</span>@endif
                </div>
            </li>
        @endforeach
        </ul>
    @endif
@endif

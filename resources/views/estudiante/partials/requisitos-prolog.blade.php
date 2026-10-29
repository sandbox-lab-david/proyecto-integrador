@if (!$requisitosMotor['disponible'])
    <p role="alert">{{ $requisitosMotor['error'] }}</p>
@elseif (empty($requisitosMotor['items']))
    <p>Este trámite no tiene condiciones adicionales.</p>
@else
    <ol class="lista-requisitos" role="list">
        @foreach ($requisitosMotor['items'] as $requisitoMotor)
            <li><div><p>{{ $requisitoMotor }}</p></div></li>
        @endforeach
    </ol>
@endif

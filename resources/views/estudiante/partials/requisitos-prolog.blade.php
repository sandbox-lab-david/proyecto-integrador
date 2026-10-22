@if (!$requisitosMotor['disponible'])
    <p role="alert">{{ $requisitosMotor['error'] }}</p>
@elseif (empty($requisitosMotor['items']))
    <p>Prolog no indica condiciones adicionales para este trámite.</p>
@else
    <ol class="lista-requisitos" role="list">
        @foreach ($requisitosMotor['items'] as $requisitoMotor)
            <li><div><p>{{ $requisitoMotor }}</p></div></li>
        @endforeach
    </ol>
@endif

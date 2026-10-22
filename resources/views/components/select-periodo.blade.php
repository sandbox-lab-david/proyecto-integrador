@props(['name' => 'periodo_id', 'selected' => null, 'soloActivos' => false, 'vacio' => 'Selecciona un periodo'])

@php
    $servicio = app(\App\Services\Academico\CatalogoAcademicoService::class);
    $periodos = $servicio->periodos($soloActivos);
    $elegido = $selected ?? $servicio->periodoActual()?->id;
@endphp

<select name="{{ $name }}" {{ $attributes->merge(['id' => $name, 'class' => 'ui-control']) }}>
    <option value="">{{ $vacio }}</option>
    @foreach ($periodos as $periodo)
        <option value="{{ $periodo->id }}" @selected((string) $elegido === (string) $periodo->id)>{{ $periodo->nombre }}{{ $periodo->activo ? ' (activo)' : '' }}</option>
    @endforeach
</select>

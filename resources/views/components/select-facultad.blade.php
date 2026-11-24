@props(['name' => 'facultad_id', 'selected' => null, 'soloActivas' => true, 'vacio' => 'Selecciona una facultad'])

@php
    $facultades = app(\App\Services\Academico\CatalogoAcademicoService::class)->facultades($soloActivas);
@endphp

<select name="{{ $name }}" {{ $attributes->merge(['id' => $name, 'class' => 'ui-control']) }}>
    <option value="">{{ $vacio }}</option>
    @foreach ($facultades as $facultad)
        <option value="{{ $facultad->id }}" @selected((string) $selected === (string) $facultad->id)>{{ $facultad->nombre }}</option>
    @endforeach
</select>

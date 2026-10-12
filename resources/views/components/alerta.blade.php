@props(['tipo' => 'info'])

@php
    $colores = [
        'exito' => 'bg-green-50 text-green-800 border-green-300',
        'error' => 'bg-red-50 text-red-800 border-red-300',
        'aviso' => 'bg-yellow-50 text-yellow-800 border-yellow-300',
        'info'  => 'bg-blue-50 text-blue-800 border-blue-300',
    ];
    $color = $colores[$tipo] ?? $colores['info'];
@endphp

<div role="alert" {{ $attributes->merge(['class' => "border rounded-lg p-4 mb-4 $color"]) }}>
    {{ $slot }}
</div>
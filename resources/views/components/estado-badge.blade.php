@props(['estado'=>null,'vista'=>'estudiante'])
<span {{ $attributes->class('estado-badge') }}>{{ $estado?->nombre ?? 'En revisión' }}</span>

@props(['name' => 'carrera_id', 'selected' => null, 'facultadId' => null, 'dependeDe' => null, 'vacio' => 'Selecciona una carrera'])

@php
    $carreras = $dependeDe && ! $facultadId
        ? collect()
        : app(\App\Services\Academico\CatalogoAcademicoService::class)->carrerasPorFacultad($facultadId ? (int) $facultadId : null);
    $id = $attributes->get('id', $name);
@endphp

<select name="{{ $name }}" {{ $attributes->merge(['id' => $id, 'class' => 'ui-control']) }}
    @if ($dependeDe) data-depende-de="{{ $dependeDe }}" data-url="{{ route('interno.carreras') }}" data-vacio="{{ $vacio }}" @endif>
    <option value="">{{ $vacio }}</option>
    @foreach ($carreras as $carrera)
        <option value="{{ $carrera->id }}" @selected((string) $selected === (string) $carrera->id)>{{ $carrera->nombre }}</option>
    @endforeach
</select>

@if ($dependeDe)
    @once
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('select[data-depende-de]').forEach((carreras) => {
                    const facultad = document.getElementById(carreras.dataset.dependeDe);
                    if (!facultad) return;

                    facultad.addEventListener('change', async () => {
                        carreras.innerHTML = '';
                        carreras.add(new Option(carreras.dataset.vacio, ''));
                        if (!facultad.value) return;

                        carreras.disabled = true;
                        try {
                            const respuesta = await fetch(`${carreras.dataset.url}?facultad_id=${encodeURIComponent(facultad.value)}`, {
                                headers: { Accept: 'application/json' },
                                credentials: 'same-origin',
                            });
                            if (respuesta.ok) {
                                (await respuesta.json()).forEach((c) => carreras.add(new Option(c.nombre, c.id)));
                            }
                        } finally {
                            carreras.disabled = false;
                        }
                    });
                });
            });
        </script>
    @endonce
@endif

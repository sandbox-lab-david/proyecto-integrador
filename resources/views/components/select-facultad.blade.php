<!-- 1. Definimos las "variables" que este componente acepta -->
@props(['name' => 'facultad_id', 'selected' => null])

<!-- 2. Llamamos al Contrato C01 para obtener los datos internamente -->
@php
    // Inyectamos el servicio académico y pedimos la lista de facultades (usando el stub que ya creaste)
    $servicio = app(App\Services\Academico\CatalogoAcademicoService::class);
    $facultades = $servicio->facultades();
@endphp

<!-- 3. Dibujamos el HTML del menú desplegable -->
<!-- $attributes->merge(...) permite que los otros grupos le agreguen clases de CSS extra si quieren -->
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'border border-gray-300 rounded-lg p-2 bg-white w-full focus:ring-blue-500 focus:border-blue-500']) }}>
    
    <option value="">Seleccione una facultad...</option>
    
    <!-- 4. Hacemos un bucle para dibujar cada opción -->
    @foreach($facultades as $facultad)
        <option value="{{ $facultad->id }}" {{ $selected == $facultad->id ? 'selected' : '' }}>
            {{ $facultad->nombre }}
        </option>
    @endforeach

</select>
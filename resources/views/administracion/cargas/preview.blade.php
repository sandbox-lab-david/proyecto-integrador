@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Vista Previa de Importación ({{ ucfirst($tipoCarga) }})</h1>
        <a href="{{ route('cargas.index') }}" class="text-sm text-blue-600 hover:underline">← Cancelar y volver</a>
    </div>

    {{-- Resumen de Validación --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
            <span class="text-sm text-gray-600">Filas Válidas</span>
            <p class="text-xl font-bold text-green-700">{{ $resumen['correctas'] }}</p>
        </div>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded shadow-sm">
            <span class="text-sm text-gray-600">Duplicados</span>
            <p class="text-xl font-bold text-yellow-700">{{ $resumen['duplicados'] }}</p>
        </div>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
            <span class="text-sm text-gray-600">Errores de Formato</span>
            <p class="text-xl font-bold text-red-700">{{ $resumen['errores'] }}</p>
        </div>
    </div>

    {{-- Tabla de Previsualización con Colores --}}
    <div class="bg-white rounded-lg shadow-md overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Fila</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Identificación / Código</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Nombre / Detalle</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Estado de Validación</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700">Observación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($filas as $fila)
                    {{-- Lógica de resaltado según estado --}}
                    @php
                        $bgColor = 'bg-white';
                        if ($fila['estado'] === 'error') $bgColor = 'bg-red-100 text-red-800';
                        elseif ($fila['estado'] === 'duplicado') $bgColor = 'bg-yellow-100 text-yellow-800';
                        elseif ($fila['estado'] === 'valido') $bgColor = 'bg-green-50 text-green-800';
                    @endphp
                    <tr class="{{ $bgColor }}">
                        <td class="px-4 py-3 font-semibold">{{ $fila['num_fila'] }}</td>
                        <td class="px-4 py-3">{{ $fila['codigo'] }}</td>
                        <td class="px-4 py-3">{{ $fila['nombre'] }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $fila['estado'] === 'error' ? 'bg-red-200 text-red-800' : '' }}
                                {{ $fila['estado'] === 'duplicado' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                {{ $fila['estado'] === 'valido' ? 'bg-green-200 text-green-800' : '' }}">
                                {{ strtoupper($fila['estado']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $fila['observacion'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Botón de Confirmación --}}
    <form action="{{ route('cargas.procesar') }}" method="POST">
        @csrf
        <input type="hidden" name="batch_id" value="{{ $batchId }}">
        <div class="flex justify-end gap-3">
            <a href="{{ route('cargas.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 text-sm">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-semibold" {{ $resumen['correctas'] == 0 ? 'disabled' : '' }}>
                Confirmar y Procesar Registros Válidos
            </button>
        </div>
    </form>
</div>
@endsection
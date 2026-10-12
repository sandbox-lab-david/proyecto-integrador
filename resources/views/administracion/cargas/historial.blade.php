@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Historial de Importaciones SIAC</h1>
        <a href="{{ route('cargas.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">+ Nueva Carga</a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Archivo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Tipo de Carga</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Fecha y Hora</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Usuario</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Filas Creadas</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500">Reporte de Errores</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($historial as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-700">#{{ $item->id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->nombre_archivo }}</td>
                        <td class="px-4 py-3 capitalize">{{ $item->tipo_carga }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->user->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3 text-center font-bold text-green-600">{{ $item->filas_creadas }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($item->filas_errores > 0)
                                <a href="{{ route('cargas.reporte-errores', $item->id) }}" class="px-3 py-1 bg-red-100 text-red-700 rounded-full hover:bg-red-200 text-xs font-semibold inline-flex items-center gap-1">
                                    📥 Descargar Errores ({{ $item->filas_errores }})
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Sin errores</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No hay registros de cargas realizadas anteriormente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
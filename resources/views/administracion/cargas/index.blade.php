@extends('layouts.app') {{-- O la plantilla base que usen tus compañeros --}}

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Carga e Importación SIAC</h1>

    {{-- Sección de Descarga de Plantillas --}}
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-lg font-semibold mb-3 text-gray-700">1. Descargar Plantillas Oficiales</h2>
        <p class="text-sm text-gray-600 mb-4">Descargue la plantilla en blanco según el tipo de información que desea registrar.</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('cargas.plantilla', 'estudiantes') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm border flex items-center gap-2">
                📄 Plantilla Estudiantes
            </a>
            <a href="{{ route('cargas.plantilla', 'notas') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm border flex items-center gap-2">
                📄 Plantilla Notas
            </a>
            <a href="{{ route('cargas.plantilla', 'mallas') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm border flex items-center gap-2">
                📄 Plantilla Mallas
            </a>
        </div>
    </div>

    {{-- Formulario de Carga --}}
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">2. Subir Archivo de Carga</h2>
        
        <form action="{{ route('cargas.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label for="tipo_carga" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Carga</label>
                <select name="tipo_carga" id="tipo_carga" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Seleccione el tipo de información --</option>
                    <option value="estudiantes">Estudiantes</option>
                    <option value="notas">Notas</option>
                    <option value="mallas">Mallas Curriculares</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="archivo_excel" class="block text-sm font-medium text-gray-700 mb-1">Adjuntar Archivo (.xlsx, .csv)</label>
                <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls, .csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('cargas.historial') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm">Ver Historial</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-semibold">Cargar y Previsualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
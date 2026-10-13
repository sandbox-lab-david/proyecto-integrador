@extends('layouts.app')

@section('contenido')
<div class="max-w-6xl mx-auto bg-white p-6 sm:p-8 rounded-lg shadow border border-gray-200">
    <!-- Encabezado -->
    <div class="mb-8 border-b pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Plantillas Word</h1>
            <p class="text-gray-500 text-sm mt-1">Trámite: Examen de recuperación</p>
        </div>
        <a href="/tramites" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-semibold text-sm transition">
            &larr; Volver al Catálogo
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Subida de archivo e Historial -->
        <div class="lg:col-span-2">
            <h2 class="text-lg font-bold text-blue-900 mb-4">1. Subir nueva plantilla (.docx)</h2>
            
            <!-- Zona de arrastrar y soltar -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition duration-200 mb-8">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-sm text-gray-600 mb-3">Arrastra y suelta tu archivo oficial en Word aquí, o</p>
                <label class="cursor-pointer bg-blue-50 text-blue-700 font-semibold px-4 py-2 rounded border border-blue-200 hover:bg-blue-100 transition">
                    Examinar archivos
                    <input type="file" accept=".docx" class="hidden">
                </label>
                <p class="text-xs text-gray-400 mt-4">Solo se permiten archivos de Microsoft Word (.docx) hasta 5MB</p>
            </div>

            <h2 class="text-lg font-bold text-blue-900 mb-4">2. Historial de Versiones</h2>
            <div class="overflow-hidden border border-gray-200 rounded-lg">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 font-semibold text-gray-700">Versión</th>
                            <th class="p-3 font-semibold text-gray-700">Fecha de subida</th>
                            <th class="p-3 font-semibold text-gray-700">Estado</th>
                            <th class="p-3 font-semibold text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <!-- Fila de ejemplo 1 -->
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="p-3 text-gray-800 font-medium">v2.0 (Actual)</td>
                            <td class="p-3 text-gray-600">08/10/2026</td>
                            <td class="p-3"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">Activa</span></td>
                            <td class="p-3"><button type="button" class="text-blue-600 hover:text-blue-800 font-medium">Descargar</button></td>
                        </tr>
                        <!-- Fila de ejemplo 2 -->
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="p-3 text-gray-500">v1.0</td>
                            <td class="p-3 text-gray-500">01/09/2026</td>
                            <td class="p-3"><span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold">Inactiva</span></td>
                            <td class="p-3"><button type="button" class="text-blue-600 hover:text-blue-800 font-medium">Descargar</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Guía de marcadores -->
        <div>
            
            <div class="bg-blue-50 border border-blue-200 p-5 rounded-lg shadow-sm sticky top-6">
                <h3 class="font-bold text-blue-900 flex items-center mb-3">
                    <!-- Icono de información -->
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Marcadores Disponibles
                </h3>
                <p class="text-sm text-gray-700 mb-4 leading-relaxed">
                    Escribe estas variables exactamente así dentro de tu documento Word. El sistema las reemplazará de forma automática con los datos reales del estudiante.
                </p>
                
                <ul class="space-y-3 text-sm">
                    <li class="bg-white p-3 border border-blue-100 rounded shadow-sm">
                        <code class="font-bold text-blue-700 block mb-1">${nombre}</code>
                        <span class="text-gray-600 text-xs">Nombre completo del estudiante.</span>
                    </li>
                    <li class="bg-white p-3 border border-blue-100 rounded shadow-sm">
                        <code class="font-bold text-blue-700 block mb-1">${codigo}</code>
                        <span class="text-gray-600 text-xs">Código de estudiante.</span>
                    </li>
                    <li class="bg-white p-3 border border-blue-100 rounded shadow-sm">
                        <code class="font-bold text-blue-700 block mb-1">${carrera}</code>
                        <span class="text-gray-600 text-xs">Carrera del estudiante.</span>
                    </li>
                    <li class="bg-white p-3 border border-blue-100 rounded shadow-sm">
                        <code class="font-bold text-blue-700 block mb-1">${materias}</code>
                        <span class="text-gray-600 text-xs">Inserta una tabla con las materias elegidas.</span>
                    </li>
                    <li class="bg-white p-3 border border-blue-100 rounded shadow-sm">
                        <code class="font-bold text-blue-700 block mb-1">${fecha_solicitud}</code>
                        <span class="text-gray-600 text-xs">Fecha de emisión del documento.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
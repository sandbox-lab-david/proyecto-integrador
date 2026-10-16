@extends('layouts.app')

@section('contenido')
<div class="max-w-4xl mx-auto bg-white p-6 sm:p-8 rounded-lg shadow border border-gray-200">
    <div class="mb-8 border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Configuración de Trámite</h1>
        <p class="text-gray-500 text-sm">Modifica las reglas, documentos y visibilidad de la solicitud.</p>
    </div>

    
    <form>
        
        <!-- Datos Generales -->
        <h2 class="text-lg font-bold text-blue-900 mb-4">1. Datos Generales</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre del Trámite</label>
                <input type="text" value="Examen de recuperación" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Categoría</label>
                <select class="w-full border border-gray-300 rounded p-2 bg-white">
                    <option>Evaluaciones</option>
                    <option>Homologación</option>
                    <option>Retiros y continuidad</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción (Ficha del trámite)</label>
                <textarea rows="2" class="w-full border border-gray-300 rounded p-2">Examen de recuperación para una asignatura reprobada.</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Estado de Definición</label>
                <select class="w-full border border-gray-300 rounded p-2 bg-white">
                    <option value="listo">Listo</option>
                    <option value="parcial">Parcial</option>
                    <option value="pendiente">Pendiente</option>
                </select>
            </div>
        </div>

        <!-- Reglas de Materias y Documentos -->
        <h2 class="text-lg font-bold text-blue-900 mb-4">2. Reglas y Requisitos</h2>
        <div class="bg-gray-50 p-4 rounded border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center mt-6">
                <input type="checkbox" checked class="w-5 h-5 text-blue-600 rounded">
                <label class="ml-2 text-sm font-semibold text-gray-700">El alumno debe seleccionar materias</label>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Máximo de materias permitidas</label>
                <input type="number" value="1" min="1" max="10" class="w-full border border-gray-300 rounded p-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Documentos Requeridos (Ej: Cédula, Certificado)</label>
                <input type="text" placeholder="Escribe el requisito y presiona Enter..." class="w-full border border-gray-300 rounded p-2">
                <div class="mt-2 flex gap-2">
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Registro de calificaciones</span>
                </div>
            </div>
        </div>

        <!-- Disponibilidad por Facultad -->
        <h2 class="text-lg font-bold text-blue-900 mb-4">3. Habilitado en Facultades</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-8">
            <label class="flex items-center"><input type="checkbox" checked class="mr-2"> Ingeniería</label>
            <label class="flex items-center"><input type="checkbox" checked class="mr-2"> Arquitectura y Diseño</label>
            <label class="flex items-center"><input type="checkbox" checked class="mr-2"> Ciencias de la Salud</label>
            <label class="flex items-center"><input type="checkbox" class="mr-2"> Derecho, Política y Desarrollo</label>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end gap-3 border-t pt-4">
            
            <a href="/tramites" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-semibold">Cancelar</a>
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
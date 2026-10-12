@extends('layouts.app')

@section('contenido')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Catálogo de Trámites</h1>
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Nuevo Trámite</button>
        </div>

    
        <div class="border border-gray-200 p-4 rounded mb-4">
            <div class="flex justify-between">
                <h3 class="font-bold text-lg text-gray-800">Examen de Recuperación</h3>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold">Listo</span>
            </div>
            <p class="text-gray-600 text-sm mt-1">Categoría: Evaluaciones</p>
            
            <div class="mt-4 flex gap-2">
                <a href="/tramites/configurar" class="border border-gray-300 bg-gray-50 px-3 py-1 rounded text-sm hover:bg-gray-200 inline-block">Configurar</a>
                <a href="/tramites/plantillas" class="border border-purple-300 text-purple-700 bg-purple-50 px-3 py-1 rounded text-sm hover:bg-purple-100 inline-block font-semibold">
                    Plantillas DOCX
                </a>
            </div>
        </div>
        
        <!-- Aquí puedes copiar y pegar la tarjeta de arriba para simular más trámites -->

    </div>
@endsection
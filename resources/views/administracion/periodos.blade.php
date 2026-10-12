<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periodos Académicos</title>
   
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Periodos Académicos</h1>

        <!-- Formulario -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-t-4 border-blue-600">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Crear Nuevo Periodo</h2>
            <form action="#" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre del Periodo</label>
                    <input type="text"  class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                    <input type="date" class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                    <input type="date" class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="flex items-center mt-6">
                    <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <label class="ml-2 text-sm font-medium text-gray-700">¿Está activo?</label>
                </div>

                <div class="md:col-span-4 mt-2">
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700 transition">
                        Guardar Periodo
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista/Tabla -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Lista de Periodos</h2>
            <p class="text-gray-500 text-sm italic"></p>
        </div>
        
    </div>
</body>
</html>
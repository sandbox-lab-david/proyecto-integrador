<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paralelos y Docentes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Paralelos y Docentes</h1>

        <!-- Formulario de Asignación -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-t-4 border-purple-600">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Asignar Docente a Paralelo</h2>
            <form action="#" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Docente</label>
                    <select class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-purple-500 focus:outline-none bg-white">
                        <option value="">Seleccione un docente...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Materia</label>
                    <select class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-purple-500 focus:outline-none bg-white">
                        <option value="">Seleccione una materia...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre del Paralelo</label>
                    <input type="text" placeholder="Ej: Paralelo A" class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-purple-500 focus:outline-none">
                </div>

                <div class="md:col-span-3 mt-2">
                    <button type="submit" class="bg-purple-600 text-white px-5 py-2 rounded-md shadow hover:bg-purple-700 transition">
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista/Tabla -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Asignaciones Actuales</h2>
            <p class="text-gray-500 text-sm italic"></p>
        </div>
        
    </div>
</body>
</html>
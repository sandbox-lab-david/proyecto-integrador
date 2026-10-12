<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias y Mallas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Materias y Pensums</h1>

        <!-- Formulario -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-t-4 border-green-600">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Registrar Nueva Materia</h2>
            <form action="#" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código de la Materia</label>
                    <input type="text" placeholder="Ej: 1" class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-green-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre de la Materia</label>
                    <input type="text" placeholder="Ej: Matematica" class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-green-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Créditos</label>
                    <input type="number" min="1" max="10" placeholder="Ej: 4" class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-green-500 focus:outline-none">
                </div>

                <div class="md:col-span-3 mt-2">
                    <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-md shadow hover:bg-green-700 transition">
                        Guardar Materia
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista/Tabla -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Malla Curricular</h2>
            <p class="text-gray-500 text-sm italic">.</p>
        </div>
        
    </div>
</body>
</html>
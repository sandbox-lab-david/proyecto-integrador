<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEES - Sistema de Gestión Académica y Trámites</title>
    <!-- Carga de Tailwind mediante Vite y CDN como respaldo -->
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- 1. SIDEBAR / MENÚ LATERAL (Color Guinda UEES) -->
        <aside class="w-64 bg-[#7A1C30] text-white flex flex-col justify-between p-6 shadow-xl">
            <div>
                <!-- Header del Sidebar con Logo/Nombre UEES -->
                <div class="flex items-center gap-3 mb-10 px-2">
                    <div class="text-3xl font-black tracking-tighter border-r-2 border-white/30 pr-3">UEES</div>
                </div>

                <!-- Opciones del Menú Principal -->
                <nav class="space-y-2">
                    <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                        Estructura Institucional
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                        Importaciones SIAC
                    </a>
                    <!-- Menú Desplegable: Mallas y Periodos -->
                    <details class="group">
                        <summary class="flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg text-red-100 hover:bg-white/10 transition cursor-pointer select-none">
                            <span>Mallas y Periodos</span>
                            <!-- Flecha indicadora -->
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>

                        <!-- Submenú desplegable -->
                        <div class="pl-4 mt-1 space-y-1">
                            <a href="{{ route('administracion.periodos') }}" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                                Periodos Académicos
                            </a>
                            <a href="{{ route('administracion.materias') }}" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                                Materias
                            </a>
                            <a href="{{ route('administracion.paralelos') }}" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                                Paralelos
                            </a>
                        </div>
                    </details>
                    <a href="{{ route('tramites.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                        Catálogo de Trámites
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                        Usuarios y Roles
                    </a>
                </nav>
            </div>

            <!-- Footer del Sidebar -->
            <div>
                <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-red-100 hover:bg-white/10 transition">
                    Configuración
                </a>
            </div>
        </aside>

        <!-- 2. CONTENEDOR PRINCIPAL -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- NAVBAR / BARRA SUPERIOR -->
            <header class="bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between shadow-sm">
                <!-- Título de la Sección Activa -->
                <h2 class="text-2xl font-bold text-gray-800">
                    Sistema de Solicitudes y Trámites
                </h2>

                <!-- Buscador e Íconos de Perfil -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-50 border border-gray-300 rounded-full px-3 py-1 shadow-sm">
                        <input type="text" placeholder="Buscar..." class="text-sm bg-transparent border-none outline-none px-2 w-48 text-gray-700">
                    </div>

                    <!-- Ícono de Usuario -->
                    <button class="w-9 h-9 rounded-full bg-[#7A1C30] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        U
                    </button>
                </div>
            </header>

            <!-- 3. ÁREA DE CONTENIDO DINÁMICO -->
            <main class="flex-1 overflow-y-auto p-8 bg-gray-100">
                <!-- Soporte para ambas convenciones de nombres -->
                @yield('content')
                @yield('contenido')
            </main>

        </div>

    </div>

</body>
</html>
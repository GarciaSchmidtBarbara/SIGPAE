<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo', 'SIGPAE')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos globales -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    @stack('estilos')

    @stack('scripts')
</head>
<body>
    <div class="layout h-screen w-full flex flex-row">
        <!-- Menú lateral -->
        <nav class="sidebar bg-fondo basis-1/5 rounded-r-3xl p-[20px] flex flex-col min-h-screen">
            <p class="text-white text-3xl font-bold my-3 text-shadow-lg ">SIGPAE</p>
            <div class="menu flex flex-col justify-between flex-1 text-white">
                <div class="menu-superior flex flex-col">
                    <hr>
                    <a href="{{ route('welcome') }}" class="{{ request()->routeIs('welcome') ? 'activo' : '' }} links">Principal</a>
                    <a href="{{ route('alumnos.principal') }}" class="{{ request()->routeIs('alumnos.principal') ? 'activo' : '' }} links">Alumnos</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Eventos</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Intervenciones</a>
                    <a href="{{ route('planDeAccion.principal') }}" class="{{ request()->routeIs('planDeAccion.principal') ? 'activo' : '' }} links">Planes de Acción</a>
                    <a href="{{ route('planillas.principal') }}" class="{{ request()->routeIs('planillas.principal') ? 'activo' : '' }} links">Planillas</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Documentos</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Reportes</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Notificaciones</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Usuarios</a>
                </div>
                <div class="menu-inferior flex flex-col">
                    <hr>
                    <a href="{{ route('perfil.principal') }}" class="{{ request()->routeIs('perfil.principal') ? 'activo' : '' }} links">Mi perfil</a>
                    <a href="https://www.untdf.edu.ar" target="_blank" class="links">Cerrar Sesión</a>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="contenido p-[30px] overflow-y-auto w-full">
            <div class="titulo-seccion">
                @yield('encabezado', 'Sección')
            </div>
            @yield('contenido')
        </main>
    </div>

    <!-- Scripts globales -->
    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>

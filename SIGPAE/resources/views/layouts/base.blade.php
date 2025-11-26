<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <div class="layout min-h-screen w-full flex flex-row">
        <!-- Menú lateral -->
        <nav class="sidebar bg-fondo basis-1/5 rounded-r-3xl p-[20px] flex flex-col">
            <p class="text-white text-3xl font-bold my-3 text-shadow-lg ">SIGPAE</p>
            <div class="menu flex flex-col justify-between flex-1 text-white">
                <div class="menu-superior flex flex-col">
                    <hr>
                    <x-nav-item
                        route="welcome"
                        label=" Principal"
                        icon="icons.icono-home"
                        exact
                    ></x-nav-item>
                    <x-nav-item
                        route="alumnos.principal"
                        label=" Alumnos"
                        icon="icons.icono-alumno"
                        exact
                    ></x-nav-item>
                    <x-nav-item
                        label=" Eventos"
                        icon="icons.icono-evento"
                        exact
                    ></x-nav-item>
                   <x-nav-item
                        route="intervenciones.principal"
                        label=" Intervenciones"
                        icon="icons.icono-intervencion"
                        exact
                    ></x-nav-item>
                   <x-nav-item
                       route="planDeAccion.principal"
                       label=" Plan de Acción"
                       icon="icons.icono-planDeAccion"
                       exact
                    ></x-nav-item>
                    <x-nav-item
                     route="planillas.principal"
                     label=" Planillas"
                     icon="icons.icono-planilla"
                     exact
                     ></x-nav-item>
                    <x-nav-item
                        label=" Documentos"
                        icon="icons.icono-documento"
                        exact
                    ></x-nav-item>
                     <x-nav-item
                        label=" Reportes"
                        icon="icons.icono-reporte"
                        exact
                    ></x-nav-item>
                     <x-nav-item
                        label=" Notificaciones"
                        icon="icons.icono-notificacion"
                        exact
                    ></x-nav-item>
                     <x-nav-item
                        route="usuarios.principal"

                        label=" Usuarios"
                        icon="icons.icono-usuario"
                        exact
                    ></x-nav-item>
                  
                </div>
                <div class="menu-inferior flex flex-col">
                    <hr>
                    <a href="{{ route('perfil.principal') }}" class="{{ request()->routeIs('perfil.principal') ? 'activo' : '' }} links">Mi perfil</a>
                    <a href="#" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="links">
                        Cerrar Sesión
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="contenido p-[30px] w-full">
            <div class="titulo-seccion">
                @yield('encabezado', 'Sección')
            </div>
            @yield('contenido')
        </main>
    </div>

    <!-- Scripts globales -->
    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- mensajes globales -->
    @if (session('success') && empty($no_global_flash))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error') && empty($no_global_flash))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif


</body>
</html>

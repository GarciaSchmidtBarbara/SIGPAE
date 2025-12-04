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

    <!--FullCalendar CSS desde CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.8/main.min.css">


    @stack('estilos')

    @stack('scripts')
</head>

<body class="bg-gray-50 font-sans text-slate-600" x-data="{ sidebarOpen: true }">
    <div class="flex h-screen overflow-hidden">
        
        <div x-show="sidebarOpen"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden">
        </div>

        <aside :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-0 lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-30 flex flex-col transition-all duration-300 ease-in-out bg-fondo shadow-2xl overflow-hidden lg:static">
            
            <div class="flex items-center justify-between h-16 bg-black/10 shadow-sm px-4 min-w-[16rem]">
                <span class="text-white text-2xl font-bold tracking-wider">SIGPAE</span>
                
                <button @click="sidebarOpen = false" class="text-white/70 hover:text-white transition-colors focus:outline-none hidden lg:block p-1 rounded-md hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto min-w-[16rem]">
                <div class="text-xs font-semibold text-indigo-200 uppercase tracking-wider mb-2 ml-2">
                    General
                </div>
                
                <x-nav-item route="welcome" label="Principal" icon="icons.icono-home" exact class="mb-1"></x-nav-item>
                <x-nav-item route="alumnos.principal" label="Alumnos" icon="icons.icono-alumno" exact></x-nav-item>
                <x-nav-item route="eventos.principal" label="Eventos" icon="icons.icono-evento" exact></x-nav-item>
                <x-nav-item route="intervenciones.principal" label="Intervenciones" icon="icons.icono-intervencion" exact></x-nav-item>
                <x-nav-item route="planDeAccion.principal" label="Plan de Acción" icon="icons.icono-planDeAccion" exact></x-nav-item>

                <div class="pt-4 pb-2">
                    <hr class="border-t border-white/10">
                    <div class="text-xs font-semibold text-indigo-200 uppercase tracking-wider mt-4 mb-2 ml-2">
                        Administración
                    </div>
                </div>

                <x-nav-item route="planillas.principal" label="Planillas" icon="icons.icono-planilla" exact></x-nav-item>
                <x-nav-item label="Documentos" icon="icons.icono-documento" exact></x-nav-item>
                <x-nav-item label="Reportes" icon="icons.icono-reporte" exact></x-nav-item>
                <x-nav-item route="usuarios.principal" label="Usuarios" icon="icons.icono-usuario" exact></x-nav-item>
            </nav>
            
            <div class="p-4 bg-black/20 border-t border-white/10 min-w-[16rem]">
                <a href="{{ route('perfil.principal') }}" class="flex items-center gap-3 text-white hover:text-indigo-200 transition-colors mb-3">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <span class="text-sm font-medium">Mi Perfil</span>
                </a>
                
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                   class="flex items-center gap-3 text-red-200 hover:text-red-100 transition-colors text-sm font-medium group">
                    <div class="w-8 h-8 rounded-full bg-red-500/10 group-hover:bg-red-500/20 flex items-center justify-center">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    Cerrar Sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b shadow-sm lg:bg-transparent lg:border-none lg:shadow-none lg:py-2">
                
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none hover:text-indigo-600 hover:bg-gray-200/50 p-2 rounded-md transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <span class="text-lg font-bold text-gray-700 lg:hidden">SIGPAE</span>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-[30px] pt-2">
                <div class="titulo-seccion mb-6">
                    @yield('encabezado', 'Sección')
                </div>
                
                @if (session('success') && empty($no_global_flash))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 shadow-sm rounded-r" role="alert">
                        <p class="font-bold">Éxito</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error') && empty($no_global_flash))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 shadow-sm rounded-r" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @yield('contenido')
            </main>
        </div>
    </div>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>

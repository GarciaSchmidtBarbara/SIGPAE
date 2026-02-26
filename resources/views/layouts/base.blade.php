<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'SIGPAE')</title>
     
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Estilos globales -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    {{-- Estilos de impresión para Vista Previa --}}
    <style>
        @media print {
            * { background: transparent !important; color: #000 !important; }
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .no-imprimir { display: none !important; }
            .fila-botones { display: none !important; }
            .sidebar { display: none !important; }
            aside { display: none !important; }
            header { display: none !important; }
            .titulo-seccion { display: none !important; }
            .bg-white { background: white !important; }
            table { border-collapse: collapse; width: 100%; }
            table, th, td { border: 1px solid #000 !important; }
            th, td { padding: 8px !important; text-align: left; }
        }
    </style>

    @stack('estilos')

    <!--FullCalendar CSS desde CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.8/main.min.css">

    @stack('scripts')
</head>

<body class="bg-gray-50 font-sans text-slate-600" 
    x-data="{sidebarOpen: window.innerWidth >= 1024 }"
    x-init="window.addEventListener('resize', () => {sidebarOpen = window.innerWidth >= 1024; })"
    >
    
    <div class="flex min-h-screen">
        
        <div x-show="sidebarOpen"
             x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden">
        </div>

        <aside :class="{'translate-x-0': sidebarOpen,'-translate-x-full': !sidebarOpen}"
                class="fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out bg-fondo shadow-2xl lg:translate-x-0 lg:static lg:inset-0"
        
            <div class="flex items-center justify-between h-16 bg-black/10 shadow-sm px-4">
                <span class="text-white text-2xl font-bold tracking-wider">SIGPAE</span>
                
                <button @click="sidebarOpen = false" class="text-white/70 hover:text-white transition-colors focus:outline-none hidden lg:block p-1 rounded-md hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
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
            
            <div class="p-4 bg-black/20 border-t border-white/10">
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

        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b shadow-sm lg:bg-transparent lg:border-none lg:shadow-none lg:py-2">
                
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none hover:text-indigo-600 hover:bg-gray-200/50 p-2 rounded-md transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <span class="text-lg font-bold text-gray-700 lg:hidden">SIGPAE</span>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 pt-2">
               <div class="titulo-seccion mb-6">
                    @yield('encabezado', 'Sección')
                </div>
                
                @if (session('success') && empty($no_global_flash))
                    <div x-data="{ open: true }">
                        <x-ui.modal-alert
                            variant="success"
                            message="{{ session('success') }}"
                        />
                    </div>
                @endif

                @if (session('error') && empty($no_global_flash))
                    <div x-data="{ open: true }">
                        <x-ui.modal-error
                            variant="error"
                            message="{{ session('error') }}"
                        />
                    </div>
                @endif

                @yield('contenido')
            </main>

            <!-- Modales globales -->
            <div>
                <!-- Modal Confirmar reutilizable -->
                <div x-data="{ open: false, formId: null, message: '' }"
                     @abrir-modal-confirmar.window="
                        formId = $event.detail.formId;
                        message = $event.detail.message;
                        open = true;
                     "
                >
                    <x-ui.modal-confirmar 
                        confirmText="Confirmar" 
                        cancelText="Cancelar" 
                        event="confirm-accepted" 
                    />
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>

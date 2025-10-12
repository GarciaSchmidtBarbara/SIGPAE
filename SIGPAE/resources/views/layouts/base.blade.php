<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo', 'SIGPAE')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos globales -->
    <link href="{{ asset('css/base.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    @stack('estilos')
</head>
<body>
    <div class="layout">
        <!-- Menú lateral -->
        <nav class="sidebar">
            <p>SIGPAE</p>
            <div class="menu-divisor"></div>

            <div class="menu-superior">
                <a href="{{ route('welcome') }}" class="{{ request()->routeIs('welcome') ? 'activo' : '' }}">Principal</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Alumnos</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Eventos</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Intervenciones</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Planes de Acción</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Planillas</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Documentos</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Reportes</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Notificaciones</a>
            </div>

            <div class="menu-divisor"></div>

            <div class="menu-inferior">
                <a href="https://www.untdf.edu.ar" target="_blank">Usuarios</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Mi perfil</a>
                <a href="https://www.untdf.edu.ar" target="_blank">Cerrar Sesión</a>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="contenido">
            <div class="titulo-seccion">
                @yield('encabezado', 'Sección')
            </div>
            @yield('contenido')
        </main>
    </div>

    <!-- Scripts globales -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>

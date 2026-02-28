<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - SIGPAE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="relative min-h-screen w-full px-4 sm:px-6 
                bg-login-grid
                flex items-center justify-center">
        <div class="relative bg-white w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-2xl z-10">
            <h1 class="text-3xl sm:text-4xl font-bold text-center mb-8">Iniciar Sesión</h1>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Usuario</label>
                    <input type="text" name="usuario" required class="input-form-login w-full">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Contraseña</label>
                    <input type="password" name="contrasenia" required class="input-form-login w-full">
                </div>

                @if ($errors->any())
                    <div class="text-red-500 mt-2">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit" class="btn-ingresar mt-4">Ingresar</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('password.request') }}" class="text-blue-500 hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - SIGPAE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen w-full bg-[radial-gradient(circle_at_top_left,_#9850CF,_#6688F6)] flex items-center justify-center gap-0">
        <div class="bg-white py-8 px-25 w-4/9 rounded-2xl shadow-lg z-10">
            <p class="text-5xl pb-10 w-full text-center">Iniciar Sesión</p>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <label for="usuario">Usuario</label><br>
                <input 
                    type="text" 
                    name="usuario" 
                    required
                    class="input-form-login"
                ><br>

                <label for="contrasenia">Contraseña</label><br>
                <input
                    type="password"
                    name="contrasenia"
                    required
                    class="input-form-login"
                ><br>

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

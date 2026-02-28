<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - SIGPAE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="relative min-h-screen w-full px-4 sm:px-6 
                bg-login-grid
                flex items-center justify-center">
        <div class="relative bg-white w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-2xl z-10">
            <h1 class="text-3xl sm:text-4xl font-bold text-center mb-8">Recuperar Contraseña</h1>

            @if (session('status'))
                <div class="text-green-500 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input-form-login w-full">
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

                <button type="submit" class="btn-ingresar mt-4 w-full">
                    Enviar link de recuperación
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-blue-500 hover:underline">
                    Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</body>
</html>

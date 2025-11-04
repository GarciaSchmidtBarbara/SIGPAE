<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('Forgot password', 'SIGPAE')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Estilos globales -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen w-full bg-[radial-gradient(circle_at_top_left,_#9850CF,_#6688F6)] flex items-center justify-center gap-0">
        <div class="bg-white py-8 px-25 w-4/9 rounded-2xl shadow-lg z-10">
            <p class="text-5xl pb-10 w-full text-center">Recuperar contraseña</p>
            <!-- Mensaje de exito --> 
            @if (session('status'))
                <div class="text-green-600 text-center mb-4">{{ session('status') }}</div>
                <!-- link para ingresar token --> 
                <div class="text-center mt-4">
                    <a href="{{ route('password.enterToken') }}" class="text-blue-500 hover:underline">
                        Ingresar token de recuperación
                    </a>
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <label for="email" class="block font-medium">Correo electrónico</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    required 
                    autofocus
                    class="input-form-login"
                >
                @error('email')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn-ingresar mt-6">Generar token</button>
            </form>
            <div class="text-center mt-4">
                <a href="{{ route('login.form') }}" class="text-blue-500 hover:underline">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
</html>

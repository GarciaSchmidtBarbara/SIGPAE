<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen w-full bg-[radial-gradient(circle_at_top_left,_#9850CF,_#6688F6)] flex items-center justify-center">
        <div class="bg-white py-8 px-10 w-4/9 rounded-2xl shadow-lg">
            <p class="text-4xl pb-6 text-center font-semibold">Restablecer Contraseña</p>

            @if ($errors->any())
                <div class="text-red-500 mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label for="email" class="block font-medium">Correo electrónico</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    required 
                    class="input-form-login"
                >

                <label for="password" class="block font-medium mt-4">Nueva contraseña</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    required 
                    minlength="8"
                    class="input-form-login"
                >

                <label for="password_confirmation" class="block font-medium mt-4">Confirmar nueva contraseña</label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    required 
                    minlength="8"
                    class="input-form-login"
                >

                <button type="submit" class="btn-ingresar mt-6">Restablecer contraseña</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login.form') }}" class="text-blue-500 hover:underline">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
</html>

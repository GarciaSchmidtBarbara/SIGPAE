<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña - SIGPAE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen w-full bg-[radial-gradient(circle_at_top_left,_#9850CF,_#6688F6)] flex items-center justify-center gap-0">
        <div class="bg-white py-8 px-25 w-4/9 rounded-2xl shadow-lg z-10">
            <p class="text-5xl pb-10 w-full text-center">Restablecer Contraseña</p>

            @if (session('status'))
                <div class="text-green-500 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <label for="email">Correo electrónico</label><br>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email', $request->email) }}" 
                    required 
                    autofocus
                    class="input-form-login"
                ><br>

                <!-- Nueva contraseña -->
                <label for="password" class="mt-4">Contraseña</label><br>
                <input 
                    type="password" 
                    name="password" 
                    required
                    class="input-form-login"
                ><br>

                <!-- Confirmar contraseña -->
                <label for="password_confirmation" class="mt-4">Confirmar Contraseña</label><br>
                <input 
                    type="password" 
                    name="password_confirmation" 
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

                <button type="submit" class="btn-ingresar mt-4 w-full">
                    Restablecer Contraseña
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

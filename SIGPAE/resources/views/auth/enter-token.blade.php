<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Token</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-[radial-gradient(circle_at_top_left,_#9850CF,_#6688F6)]">
        <div class="bg-white p-10 rounded-2xl shadow-lg w-96">
            <h2 class="text-2xl font-bold text-center mb-6">Ingresar Token</h2>

            @if (session('status'))
                <div class="text-green-600 text-center mb-4">{{ session('status') }}</div>
            @endif
            <!--Mensaje de error-->
            @if ($errors->any())
                <div class="text-red-500 text-center mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('password.verifyToken') }}">
                @csrf
                <label for="token" class="block font-medium mb-2">Token</label>
                <input type="text" name="token" id="token" class="input-form-login" required>

                <button type="submit" class="btn-ingresar mt-6 w-full">Verificar Token</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login.form') }}" class="text-blue-500 hover:underline">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
</html>

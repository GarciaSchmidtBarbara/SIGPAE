<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activación de Cuenta - SIGPAE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div x-data="{
    selectedProfesion: '',
    selectedSigla: '',
    onProfesionChange(e) {
        const opt = e.target.options[e.target.selectedIndex];
        this.selectedProfesion = opt.value || '';
        this.selectedSigla = opt.dataset.sigla || '';
    },
}"
class="relative min-h-screen w-full px-4 sm:px-6 
       bg-login-grid
       flex items-center justify-center">

    <div class="relative bg-white w-full max-w-4xl p-6 sm:p-8 rounded-2xl shadow-2xl z-10">

        <h1 class="text-3xl sm:text-4xl font-bold text-center mb-4">
            Bienvenido a SIGPAE
        </h1>

        <p class="text-sm text-gray-500 text-center mb-8">
            Antes de comenzar necesitamos completar tu información.
        </p>

        @if($errors->any())
            <div class="text-red-500 mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('activar.cuenta.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Fecha nacimiento --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento"
                        value="{{ old('fecha_nacimiento') }}" required
                        class="input-form-login w-full">
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono') }}" required
                        class="input-form-login w-full">
                </div>

                {{-- Profesión --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">Profesión</label>
                    <select name="profesion" @change="onProfesionChange"
                            required class="input-form-login w-full">
                        <option value="">Seleccione profesión</option>
                        @foreach(\App\Enums\Siglas::cases() as $case)
                            <option value="{{ $case->label() }}"
                                    data-sigla="{{ $case->value }}">
                                {{ $case->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Siglas --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">Siglas</label>
                    <input name="siglas" readonly x-model="selectedSigla"
                        class="input-form-login w-full bg-gray-100">
                </div>

                {{-- Contraseña --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">Contraseña</label>
                    <input type="password" name="password" required
                        class="input-form-login w-full">
                </div>

                {{-- Confirmar contraseña --}}
                <div>
                    <label class="block mb-1 text-sm font-medium">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required
                        class="input-form-login w-full">
                </div>

            </div>

            <button type="submit" class="btn-ingresar mt-6 w-full">
                Activar Cuenta
            </button>

        </form>
    </div>
</div>

</body>
</html>
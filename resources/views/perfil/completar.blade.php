@extends('layouts.base')

@section('contenido')
<div class="min-h-screen flex items-center justify-center px-6">

    <div class="w-full max-w-xl space-y-8">

        <!-- Encabezado -->
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">
                Bienvenido al sistema
            </h1>
            <p class="text-sm text-gray-500">
                Antes de comenzar necesitamos completar tu información.
            </p>
        </div>

        <!-- Formulario -->
        <form method="POST"
              action="{{ route('perfil.completar') }}"
              class="space-y-8 border rounded-xl p-8">
            @csrf

            <!-- Contraseña -->
            <div class="space-y-6">
                <h2 class="text-lg font-medium text-gray-700">
                    Seguridad
                </h2>

                <div>
                    <label class="label-perfil">Nueva contraseña</label>
                    <input type="password"
                           name="password"
                           required
                           class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Confirmar contraseña</label>
                    <input type="password"
                           name="password_confirmation"
                           required
                           class="input-perfil">
                </div>
            </div>

            <!-- Información profesional -->
            <div class="space-y-6">
                <h2 class="text-lg font-medium text-gray-700">
                    Información profesional
                </h2>

                <div>
                    <label class="label-perfil">Fecha de nacimiento</label>
                    <input type="date"
                           name="fecha_nacimiento"
                           required
                           class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Profesión</label>
                    <input type="text"
                           name="profesion"
                           required
                           class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Siglas</label>
                    <input type="text"
                           name="siglas"
                           required
                           class="input-perfil">
                </div>
            </div>

            <!-- Botón -->
            <div class="pt-4">
                <button type="submit"
                        class="btn-primario w-full">
                    Completar registro
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
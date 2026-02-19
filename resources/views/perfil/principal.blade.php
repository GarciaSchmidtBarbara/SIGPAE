@php($no_global_flash = true)

@extends('layouts.base')

@section('encabezado', 'Perfil')

@section('contenido')
<div>
<div class="p-2 rounded-lg text-lg font-semibold flex text-gray-400 items-center">
    <span>Visualización de historial: {{ $prof->usuario }} - {{ $prof->siglas }} ({{ $prof->profesion }})</span>
</div>
<form id="form-actualizar" 
    action="{{ route('perfil.actualizar') }}"
    method="POST"
    class="space-y-6">
    @csrf
    <div class="informacion">
        <p class="separador">Mi información personal</p>
        <div class="my-2 px-4">
            <div>
                <p>Nombre</p>
                <input type="text" name="nombre" value="{{ $prof->persona->nombre }}" class="form-input">
            </div>
            <div>
                <p>Apellido</p>
                <input type="text" name="apellido" value="{{ $prof->persona->apellido }}" class="form-input">
            </div>
            <div>
                <p>Profesión</p>
                <input type="text" name="profesion" value="{{ $prof->profesion }}" class="form-input">
            </div>
            <div>
                <p>Siglas</p>
                <input type="text" name="siglas" value="{{ $prof->siglas }}" class="form-input">
            </div>
        </div>
    </div>
    <div class="informacion">
        <p class="separador">Credenciales</p>
        <div class="my-2 px-4">
            <div>
                <p>Usuario</p>
                <input type="text" name="usuario" value="{{ $prof->usuario }}" class="form-input">
            </div>
            <div>
                <p>Mail registrado</p>
                <input type="email" name="email" value="{{ $prof->email }}" class="form-input">
            </div>
            <div>
                <p>Contraseña</p>
                <input type="password" placeholder="********" class="form-input" disabled>
            </div>
        </div>
    </div>
    <div class="flex justify-end mx-5 gap-4">
        <button type="button" class="btn-eliminar">Eliminar cuenta</button>
        <button type="submit" class="btn-aceptar">Guardar</button>
    </div>
</form>

<div>
<div x-data="{ open: false }">
    <button @click="open = true"
        class="btn-aceptar">
        Cambiar contraseña
    </button>

    <!-- Modal -->
    <x-ui.modal title="Cambiar contraseña" x-show="open">
        <form method="POST" action="{{ route('perfil.cambiar-contrasenia') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Contraseña actual</label>
                <input id="current_password" name="current_password" type="password" class="form-input w-full" required>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
                <input id="password" name="password" type="password" class="form-input w-full" required>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar nueva contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-input w-full" required>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" @click="open = false" class="btn-eliminar">
                    Cancelar
                </button>
                <button type="submit" class="btn-aceptar">
                    Guardar
                </button>
            </div>
        </form>

    </x-ui.modal>
</div>
<div>
</div>
<div></div>

@endsection
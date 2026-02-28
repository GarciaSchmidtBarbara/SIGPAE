@php($no_global_flash = true)
@extends('layouts.base')

@section('encabezado', 'Mi perfil')

@section('contenido')
<div class="max-w-4xl mx-auto px-4 space-y-4">

    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-semibold text-gray-800">
            {{ $prof->persona->nombre }} {{ $prof->persona->apellido }}
        </h2>
        <p class="text-sm text-gray-500">
            {{ $prof->usuario }} · {{ $prof->siglas }} · {{ $prof->profesion }}
        </p>
    </div>

    <form id="form-actualizar"
        action="{{ route('perfil.actualizar') }}"
        method="POST"
        class="space-y-10">
        @csrf

        <!-- Información personal -->
        <section class="space-y-4 border-b pb-4">
            <h3 class="separador">Información personal</h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="label-perfil">Nombre</label>
                    <input type="text" name="nombre"
                        value="{{ $prof->persona->nombre }}"
                        class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Apellido</label>
                    <input type="text" name="apellido"
                        value="{{ $prof->persona->apellido }}"
                        class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Documento</label>
                    <input type="text" name="documento"
                        value="{{ $prof->persona->dni }}"
                        disabled
                        class="input-perfil opacity-70 cursor-not-allowed">
                </div>

                <div>
                    <label class="label-perfil">Telefono</label>
                    <input type="text" name="telefono"
                        value="{{ $prof->persona->telefono }}"
                        class="input-perfil">
                </div>
                
                <div>
                    <label class="label-perfil">Profesión</label>
                    <input type="text" name="profesion"
                        value="{{ $prof->profesion }}"
                        class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Siglas</label>
                    <input type="text" name="siglas"
                        value="{{ $prof->siglas }}"
                        class="input-perfil">
                </div>
            </div>
        </section>

        <!-- Credenciales -->
        <section class="space-y-4 border-b pb-4">
            <h3 class="separador">Credenciales</h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="label-perfil">Usuario</label>
                    <input type="text" name="usuario"
                        value="{{ $prof->usuario }}"
                        class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Mail registrado</label>
                    <input type="email" name="email"
                        value="{{ $prof->email }}"
                        class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Contraseña</label>
                    <input type="password"
                        placeholder="********"
                        disabled
                        class="input-perfil opacity-60 cursor-not-allowed">
                </div>
            </div>
        </section>

        <!-- Preferencias -->

        <!-- Botones -->
        <div class="flex justify-between items-center">
            <button type="button" class="text-red-500 hover:text-red-600 text-sm">
                Eliminar cuenta
            </button>

            <div class="flex gap-4">
                <button type="button"
                    @click="open = true"
                    class="btn-secundario">
                    Cambiar contraseña
                </button>

                <button type="submit"
                    class="btn-primario">
                    Guardar cambios
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
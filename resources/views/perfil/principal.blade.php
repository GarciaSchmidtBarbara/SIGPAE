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
                        value="{{ $prof->telefono }}"
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
        <section class="space-y-4 border-b pb-4">
            <h3 class="separador">Preferencias</h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="label-perfil">Hora de envio del resumen diario</label>
                    <input type="text" name="hora_envio_resumen_diario"
                        value="{{ old('hora_envio_resumen_diario') }}"
                        class="input-perfil">
                </div>

                 <div>
                    <label class="label-perfil">
                        Anticipación aviso de eventos (minutos)
                    </label>
                    <select name="notification_anticipation_minutos"
                        class="input-perfil">

                        <option value="10" 
                            {{ old('notification_anticipation_minutos', $prof->notification_anticipation_minutos) == 10 ? 'selected' : '' }}>
                            10 minutos
                        </option>
                        <option value="30" 
                            {{ old('notification_anticipation_minutos', $prof->notification_anticipation_minutos) == 30 ? 'selected' : '' }}>
                            30 minutos
                        </option>
                        <option value="60" 
                            {{ old('notification_anticipation_minutos', $prof->notification_anticipation_minutos) == 60 ? 'selected' : '' }}>1
                             hora
                        </option>
                        <option value="120" 
                            {{ old('notification_anticipation_minutos', $prof->notification_anticipation_minutos) == 120 ? 'selected' : '' }}>
                            2 horas
                        </option>
                        <option value="1440" 
                            {{ old('notification_anticipation_minutos', $prof->notification_anticipation_minutos) == 1440 ? 'selected' : '' }}>
                            1 día
                        </option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Botones -->
        <div class="flex justify-between items-center">

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

    <!-- Modal cambiar estado -->
    <div x-data="modalPerfil()">

        <button 
            @click="abrir()"
            class="btn-eliminar">
            Desactivar cuenta
        </button>

        <!-- Modal -->
        <div x-show="mostrar"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="cerrar()"
            class="fixed inset-0 z-50 flex items-center justify-center"
            role="dialog">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50"
                @click="cerrar()"></div>

                <!-- Panel -->
                <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-xl p-6">

                    <h3 class="text-lg font-semibold mb-4">
                        ¿Desea desactivar su cuenta?
                    </h3>

                    <p class="text-gray-600 mb-6">
                        Tu cuenta pasará a estado inactivo y se cerrará su sesión.
                    </p>

                    <form method="POST" action="{{ route('perfil.desactivar') }}">
                        @csrf
                        @method('PATCH')

                        <div class="flex justify-end gap-3">
                            <button type="button"
                                    @click="cerrar()"
                                    class="btn-volver">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn-eliminar">
                                Desactivar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function modalPerfil() {
    return {
        mostrar: false,

        abrir() {
            this.mostrar = true
        },

        cerrar() {
            this.mostrar = false
        }
    }
}
</script>
@endsection
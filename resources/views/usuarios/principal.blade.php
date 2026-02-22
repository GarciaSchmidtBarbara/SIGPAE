@extends('layouts.base')

@section('encabezado', 'Usuarios')

@section('contenido')
<div class="p-6" x-data="estadoUsuario()"
     @abrir-modal-estado.window="abrir($event.detail)">

    <form id="form-usuario" method="GET" action="{{ route('usuarios.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        <a class="btn-aceptar" href="{{ route('usuarios.crear-editar') }}">Registrar Usuario</a>
        <input name="nombre" placeholder="Nombre" class="border px-2 py-1 rounded w-1/5">
        <input name="apellido" placeholder="Apellido" class="border px-2 py-1 rounded w-1/5">
        <input name="documento" placeholder="Documento" class="border px-2 py-1 rounded w-1/5">
        <select name="profesion" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todas las profesiones</option>

            @foreach($siglas as $sigla)
            <option value="{{ $sigla }}" {{ request('profesion') === $sigla ? 'selected' : '' }}>
                {{ $sigla }}
            </option>
            @endforeach
        </select>

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('usuarios.principal') }}" >Limpiar</a>
    </form>

    <x-tabla-dinamica 
        :columnas="[
            ['key' => 'persona.nombre', 'label' => 'Nombre'],
            ['key' => 'persona.apellido', 'label' => 'Apellido'],
            ['key' => 'persona.dni', 'label' => 'Documento'],
            ['key' => 'siglas', 'label' => 'Profesión'],
        ]"
        :filas="$usuarios"
        idCampo="id_profesional"
        :acciones="fn($fila) => view('components.boton-estado', [
            'activo' => data_get($fila, 'persona.activo'),
            'route' => route('usuarios.cambiarActivo', data_get($fila, 'id_profesional')),
            'text_activo' => 'Desactivar',
            'text_inactivo' => 'Activar',
            'message_activo' => '¿Desea desactivar este usuario?',
            'message_inactivo' => '¿Desea activar este usuario?',
        ])->render()"
    >
    </x-tabla-dinamica>

    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>

    <!-- Modal cambiar estado -->
    <div x-show="mostrarModal"
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
                ¿Confirmar desactivación?
            </h3>

            <p class="text-gray-600 mb-6">
                El usuario pasará a estado inactivo.
            </p>

            <form method="POST" :action="route">
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

<script>
function estadoUsuario() {
    return {
        mostrarModal: false,
        route: null,

        abrir(data) {
            this.route = data.route
            this.mostrarModal = true
        },

        cerrar() {
            this.mostrarModal = false
            this.route = null
        }
    }
}
</script>
@endsection

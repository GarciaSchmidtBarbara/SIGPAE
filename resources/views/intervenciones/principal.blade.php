@extends('layouts.base')

@section('encabezado', 'Todas las Intervenciones')

{{--ESTE H2 COLOCA EL TITULO AL DOCUMENTO--}}
<h2 class="page-title-print" style="display: none;">@yield('encabezado') </h2>

@section('contenido')

<div class="p-6" x-data="intervencionesData()"
     @abrir-modal-eliminar.window="abrir($event.detail)">    <form id="form-intervencion" method="GET" action="{{ route('intervenciones.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        
        <a class="btn-aceptar" href="{{ route('intervenciones.crear') }}">Crear Intervención</a>
        
        <select name="tipo_intervencion" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los tipos</option>
            @foreach($tiposIntervencion as $tipo)
                <option value="{{ $tipo }}" {{ request('tipo_intervencion') === $tipo ? 'selected' : '' }}>
                    {{ $tipo }}
                </option>
            @endforeach
        </select>

        <input name="nombre" value="{{ request('nombre') }}" placeholder="Nombre/DNI" class="border px-2 py-1 rounded w-1/5">

        <select name="aula" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($aulas as $curso)
                <option value="{{ $curso->id }}" {{ request('aula') == $curso->id ? 'selected' : '' }}>
                    {{ $curso->descripcion }}
                </option>
            @endforeach
        </select>

        <p>Desde</p>
        <input type="date" name="fecha_desde" class="border px-2 py-1 rounded" value="{{ request('fecha_desde') }}">
        <p>Hasta</p>
        <input type="date" name="fecha_hasta" class="border px-2 py-1 rounded" value="{{ request('fecha_hasta') }}">

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('intervenciones.principal') }}" >Limpiar</a>   
    </form>

    {{--ENVOLVER LA TABLA A IMPRIMIR--}}
    <div class="data-table-to-print">
        <x-tabla-dinamica 
            :columnas="[
                ['key' => 'fecha_hora_intervencion', 'label' => 'Fecha y Hora'],
                ['key' => 'tipo_intervencion', 'label' => 'Tipo'],
                ['key' => 'alumnos', 'label' => 'Destinatarios'],
                ['key' => 'profesionales', 'label' => 'Intervinientes'],
            ]"
            :filas="$intervenciones"
            :acciones="fn($fila) => view('components.boton-eliminar', [
                'route' => route('intervenciones.eliminar', $fila['id_intervencion']),
                'texto' => 'Eliminar'
            ])->render()"
            idCampo="id_intervencion"
            class="tabla-imprimir" {{--PONERLE LA CLASE A LA TABLA A IMPRIMIR--}}
            :filaEnlace="fn($fila) => route('intervenciones.editar', data_get($fila, 'id_intervencion'))"
        >
            <x-slot:accionesPorFila>
                @once
                    @php
                        $accionesPorFila = function ($fila) {
                            $activo = data_get($fila, 'activo');
                            $ruta = route('intervenciones.cambiarActivo', data_get($fila, 'id_intervencion'));
                            return view('components.boton-estado', [
                                'activo' => $activo,
                                'route' => $ruta
                            ])->render();
                        };
                    @endphp
                @endonce
            </x-slot:accionesPorFila>
        </x-tabla-dinamica>
    </div>

    <!-- Modal eliminar -->
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
                ¿Confirmar eliminación?
            </h3>

            <p class="text-gray-600 mb-6">
                ¿Seguro que querés eliminar esta intervención?
            </p>

            <form method="POST" :action="route">
                @csrf
                @method('DELETE')

                <div class="flex justify-end gap-3">
                    <button type="button"
                            @click="cerrar()"
                            class="btn-volver">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="btn-eliminar">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="fila-botones mt-8">
        {{--ESTE BOTON BUSCA LA TABLA A IMPRIMIR--}}
        <button type="button" class="btn-aceptar btn-print-table no-print">Imprimir listado</button> 
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
<script>
function intervencionesData() {
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


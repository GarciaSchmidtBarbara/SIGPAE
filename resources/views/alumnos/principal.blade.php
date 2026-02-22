@extends('layouts.base')

@section('encabezado', 'Alumnos')
<h2 class="page-title-print" style="display: none;">@yield('encabezado') </h2>

@section('contenido')

<div class="p-6" x-data="estadoAlumno()"
     @abrir-modal-estado.window="abrir($event.detail)">
    <form id="form-alumno" method="GET" action="{{ route('alumnos.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        <a class="btn-aceptar" href="{{ route('alumnos.crear') }}">Registrar Alumno</a>
        <input name="nombre" placeholder="Nombre" class="border px-2 py-1 rounded w-1/5">
        <input name="apellido" placeholder="Apellido" class="border px-2 py-1 rounded w-1/5">
        <input name="documento" placeholder="Documento" class="border px-2 py-1 rounded w-1/5">
        <select name="aula" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($cursos as $curso)
                <option value="{{ $curso }}" {{ request('aula') === $curso ? 'selected' : '' }}>
                    {{ $curso }}
                </option>
            @endforeach
        </select>
        <select name="estado" class="border px-2 py-1 rounded w-1/5">
            <option value="" {{ request('estado', 'activos')  === '' ? 'selected' : '' }}>Todos</option>
            <option value="activos" {{ request('estado', 'activos') === 'activos' ? 'selected' : '' }}>Activos</option>
            <option value="inactivos" {{ request('estado', 'activos')  === 'inactivos' ? 'selected' : '' }}>Inactivos</option>
        </select>

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('alumnos.principal') }}" >Limpiar</a>
    </form>

    @php
        $formatters = [
            'cud' => fn($valor) => $valor ? 'Tiene' : 'No tiene',
        ];

        $accionesPorFila = function ($fila) {
            $activo = data_get($fila, 'persona.activo');
            $ruta = route('alumnos.cambiarActivo', data_get($fila, 'id_alumno'));
            return view('components.boton-estado', [
                'activo' => $activo,
                'route' => $ruta
            ])->render();
        };
    @endphp
    
<div class="data-table-to-print">
    <x-tabla-dinamica 
        :columnas="[
            ['key' => 'persona.nombre', 'label' => 'Nombre'],
            ['key' => 'persona.apellido', 'label' => 'Apellido'],
            ['key' => 'persona.dni', 'label' => 'Documento'],
            ['key' => 'aula.descripcion', 'label' => 'Aula'],
            ['key' => 'cud', 'label' => 'CUD'],
        ]"
        :filas="$alumnos"
        :formatters="$formatters"
        :acciones="fn($fila) => view('components.boton-estado', [
            'activo' => data_get($fila, 'persona.activo'),
            'route' => route('alumnos.cambiarActivo', data_get($fila, 'id_alumno')),
            'text_activo' => 'Desactivar',  
            'text_inactivo' => 'Activar',
        ])->render()"

        idCampo="id_alumno"
        class="tabla-imprimir"
        :filaEnlace="fn($fila) => route('alumnos.editar', data_get($fila, 'id_alumno'))"
    >
        <x-slot:accionesPorFila>
            @php
                // función anónima que recibirá $fila
            @endphp
            @once
                @php
                    $accionesPorFila = function ($fila) {
                        $activo = data_get($fila, 'persona.activo');
                        $ruta = route('alumnos.cambiarActivo', data_get($fila, 'id_alumno'));
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
            El alumno pasará a estado inactivo.
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

    <div class="fila-botones mt-8">
        <button type="button" class="btn-aceptar btn-print-table no-print">Imprimir listado</button>
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>

<script>
function estadoAlumno() {
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

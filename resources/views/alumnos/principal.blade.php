@extends('layouts.base')

@section('encabezado', 'Alumnos')
<h2 class="page-title-print" style="display: none;">@yield('encabezado') </h2>

@section('contenido')

<div class="px-4 py-6 md:p-6" x-data="estadoAlumno()"
     @abrir-modal-estado.window="abrir($event.detail)">
    <form id="form-alumno" x-ref="filtroForm" method="GET" action="{{ route('alumnos.principal') }}" class="flex flex-col md:flex-row gap-3 mb-6"
        x-init="
            const foco = '{{ request('foco') }}';
            if (foco) $nextTick(() => {
                const el = $refs.filtroForm.querySelector('[name=' + foco + ']');
                if (el) { el.focus(); el.setSelectionRange(el.value.length, el.value.length); }
            });
        ">
        <input type="hidden" x-ref="foco" name="foco">
        <a class="btn-aceptar" href="{{ route('alumnos.crear') }}">Registrar Alumno</a>
        
        <p class="text-sm font-semibold text-gray-600 mt-2"> Buscar alumnos </p>
        <input name="nombre" placeholder="Nombre" value="{{ request('nombre') }}" class="form-input md:w-1/5"
            @input.debounce.700ms="$refs.foco.value = 'nombre'; $refs.filtroForm.submit()">
        <input name="apellido" placeholder="Apellido" value="{{ request('apellido') }}" class="form-input md:w-1/5"
            @input.debounce.700ms="$refs.foco.value = 'apellido'; $refs.filtroForm.submit()">
        <input name="documento" placeholder="Documento" value="{{ request('documento') }}" class="form-input md:w-1/5"
            @input.debounce.700ms="$refs.foco.value = 'documento'; $refs.filtroForm.submit()">
        <select name="aula" class="form-input md:w-1/5"
            @change="$refs.filtroForm.submit()">
            <option value="">Todos los cursos</option>
            @foreach($cursos as $curso)
                <option value="{{ $curso }}" {{ request('aula') === $curso ? 'selected' : '' }}>
                    {{ $curso }}
                </option>
            @endforeach
        </select>
        <select name="estado" class="form-input md:w-1/5"
            @change="$refs.filtroForm.submit()">
            <option value="" {{ request('estado', 'activos')  === '' ? 'selected' : '' }}>Todos</option>
            <option value="activos" {{ request('estado', 'activos') === 'activos' ? 'selected' : '' }}>Activos</option>
            <option value="inactivos" {{ request('estado', 'activos')  === 'inactivos' ? 'selected' : '' }}>Inactivos</option>
        </select>

        <a class="btn-aceptar" href="{{ route('alumnos.principal') }}">Limpiar</a>
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
    
<div class="data-table-to-print bg-white rounded-xl shadow-sm overflow-hidden">
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
            'message_activo' => '¿Desea desactivar este alumno?',
            'message_inactivo' => '¿Desea activar este alumno?',
        ])->render()"

        idCampo="id_alumno"
        class="tabla-imprimir"
        :filaEnlace="fn($fila) => route('alumnos.editar', data_get($fila, 'id_alumno'))"
    >
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
    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-xl p-6 mx-4">

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
        <button type="button" class="btn-aceptar">Imprimir listado</button>
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

@extends('layouts.base')

@section('encabezado', 'Alumnos')

@section('contenido')

    {{-- Mensajes de estado --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

<div class="p-6">
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
    @endphp
    

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
        :filaEnlace="fn($fila) => route('alumnos.editar', data_get($fila, 'id_alumno'))"
    >
        <x-slot:accionesPorFila>
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

    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection

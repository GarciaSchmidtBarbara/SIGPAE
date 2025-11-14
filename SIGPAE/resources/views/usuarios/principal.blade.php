@extends('layouts.base')

@section('encabezado', 'Usuarios')

@section('contenido')
<div class="p-6">
    <form id="form-alumno" method="GET" action="{{ route('alumnos.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
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
        <a class="btn-aceptar" href="{{ route('alumnos.principal') }}" >Limpiar</a>
    </form>

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

    <x-tabla-dinamica 
        :columnas="[
            ['key' => 'persona.nombre', 'label' => 'Nombre'],
            ['key' => 'persona.apellido', 'label' => 'Apellido'],
            ['key' => 'persona.dni', 'label' => 'Documento'],
            ['key' => 'siglas', 'label' => 'Profesión'],
        ]"
        :filas="$usuarios"
        idCampo="id_usuario"
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

    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection

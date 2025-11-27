@extends('layouts.base')

@section('encabezado', 'Usuarios')

@section('contenido')
<div class="p-6">
    <form id="form-alumno" method="GET" action="{{ route('usuarios.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
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
        ])->render()"
        :filaEnlace="fn($fila) => route('usuarios.editar', data_get($fila, 'id_profesional'))"
    >
    </x-tabla-dinamica>

    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection

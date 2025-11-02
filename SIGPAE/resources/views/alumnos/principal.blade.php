@extends('layouts.base')

@section('encabezado', 'Alumnos')

@section('contenido')
<div class="p-6">
    <form id="form-alumno" class="flex gap-2 mb-6">
        <a class="btn-aceptar" href="{{ route('alumnos.crear-editar') }}">Registrar Alumno</a>
        <input name="nombre" placeholder="Nombre" class="border px-2 py-1 rounded w-1/5">
        <input name="apellido" placeholder="Apellido" class="border px-2 py-1 rounded w-1/5">
        <input name="documento" placeholder="Documento" class="border px-2 py-1 rounded w-1/5">
        <input name="curso" placeholder="Curso" class="border px-2 py-1 rounded w-1/5">
    </form>

    <x-tabla-dinamica 
        :columnas="[
            ['key' => 'persona.nombre', 'label' => 'Nombre'],
            ['key' => 'persona.apellido', 'label' => 'Apellido'],
            ['key' => 'persona.dni', 'label' => 'Documento'],
            ['key' => 'aula.descripcion', 'label' => 'Aula']
        ]"
        :filas="$alumnos"
        :acciones="[
            'eliminar' => 'alumnos.destroy',
            'eliminar_label' => 'Desactivar'
        ]"
        idCampo="id_alumno"
    />

    <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
</div>
@endsection

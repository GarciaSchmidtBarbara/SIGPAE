@extends('layouts.base')

@section('encabezado', 'Crear Usuario')

@section('contenido')

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <strong>Error:</strong>
    <ul class="mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-4xl mx-auto px-4 space-y-4">

    <!-- Header -->
    <div class="space-y-1">
        <h3 class="text-1xl font-semibold text-gray-800">
            Complete con la información personal del nuevo usuario
        </h3>
    </div>

    <form id="form-crear-user"
        action="{{ route('usuarios.store') }}"
        method="POST"
        class="space-y-6">
        @csrf
    
        <section class="space-y-4 border-b pb-4">

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="label-perfil">Nombre</label>
                    <input type="text" name="nombre" class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Apellido</label>
                    <input type="text" name="apellido" class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Documento</label>
                    <input type="text" name="dni" class="input-perfil">
                </div>

                <div>
                    <label class="label-perfil">Correo electronico</label>
                    <input type="email" name="email" class="input-perfil">
                </div>
        </section>

        <div class="flex justify-end gap-2">
            <button type="submit" class="btn-aceptar">Guardar</button>
            <a class="btn-volver" href="{{ route('usuarios.principal') }}" >Volver</a>
        </div>
    </form>

</div>
@endsection
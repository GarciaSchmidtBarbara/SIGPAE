@extends('layouts.base')

@section('encabezado', 'Crear Usuario')

@section('contenido')

<div class="max-w-4xl mx-auto px-4 space-y-4">

    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-semibold text-gray-800">
            Complete los siguientes campos requeridos
        </h2>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}"> 
        @csrf 
        <section class="space-y-4 border-b pb-4">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="">
                    <x-campo-requerido class="label-perfil" text="Nombres" required />
                    <input name="nombre" value="{{ $usuarioData['nombre'] ?? old('nombre') }}"
                        class="input-perfil">
                </div>

                <div class="">
                    <x-campo-requerido  class="label-perfil" text="Apellidos" required />
                    <input name="apellido" value="{{ $usuarioData['apellido'] ?? old('apellido') }}"
                        class="input-perfil">
                </div>

                <div class="">
                    <x-campo-requerido  class="label-perfil" text="Documento" required />
                    <input name="dni" value="{{ $usuarioData['dni'] ?? old('dni') }}"
                        class="input-perfil">
                </div>

                <div class="">
                    <x-campo-requerido class="label-perfil" text="Email" required />
                    <input name="email" value="{{ $usuarioData['email'] ?? old('email') }}"
                        class="input-perfil">
                </div>
            </div>
        </section>

        <div class="flex justify-end mt-6 gap-6"> 
            <button type="submit" class="btn-aceptar">Guardar</button> 
            <a class="btn-volver" href="{{ route('usuarios.principal') }}" >Volver</a> 
        </div>
    </form>
</div>
@endsection
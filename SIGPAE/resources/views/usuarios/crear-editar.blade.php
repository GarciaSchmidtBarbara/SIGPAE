@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Usuario' : 'Crear Usuario')

@section('contenido')
<div x-data="{
    familyMembers: {{ json_encode(array_values($familiares_temp ?? [])) }},
    
    async removeFamiliar(index) {
        if (confirm('¿Estás seguro de eliminar este familiar?')) {
            try {
                const response = await fetch(`/familiares/temp/${index}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.familyMembers.splice(index, 1);
                } else {
                    alert('Error al eliminar el familiar');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el familiar');
            }
        }
    }
}">

    <form method="POST" action="{{ isset($modo) && $modo === 'editar' 
            ? route('alumnos.actualizar', $alumno->id_alumno)
            : route('alumnos.store') }}">
        @csrf
        @if(isset($modo) && $modo === 'editar')
            @method('PUT')
        @endif
        <div class="space-y-8 mb-6">
            <p class="separador">Información Personal del Usuario</p>
            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Documento" required />
                    <input name="dni" value="{{ $usuarioData['dni'] ?? old('dni') }}" placeholder="Documento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nombres" required />
                    <input name="nombre" value="{{ $usuarioData['nombre'] ?? old('nombre') }}" placeholder="Nombres" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Apellidos" required />
                    <input name="apellido" value="{{ $usuarioData['apellido'] ?? old('apellido') }}" placeholder="Apellidos" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nombre de Usuario" required />
                    <input name="nombre-usuario" value="{{ $usuarioData['nombre-usuario'] ?? old('nombre-usuario') }}" placeholder="nombre_de_usuario" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Profesión" required />
                    <input name="profesion" value="{{ $usuarioData['profesion'] ?? old('profesion') }}" placeholder="Profesión" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Siglas de Profesión" required />
                    <input name="siglas-profesion" value="{{ $usuarioData['siglas-profesion'] ?? old('siglas-profesion') }}" placeholder="PS" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Email" required />
                    <input name="email" value="{{ $usuarioData['email'] ?? old('email') }}" placeholder="email@.com" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Contraseña" required />
                    <input name="contrasenia" value="{{ $usuarioData['contrasenia'] ?? old('contrasenia') }}" placeholder="Contr4S3ñ4_S3gur4" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Confirmar Contraseña" required />
                    <input name="confirmar-contrasenia" value="{{ $usuarioData['confirmar-contrasenia'] ?? old('confirmar-contrasenia') }}" placeholder="Contr4S3ñ4_S3gur4" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="space-y-8">
        </div>


        <div class="fila-botones mt-8">
            <button type="submit" class="btn-aceptar">Guardar</button>
            <button type="button" class="btn-eliminar" >Desactivar</button>
            <a class="btn-volver" href="{{ route('alumnos.principal') }}" >Volver</a>
        </div>
    </form>
</div>
@endsection
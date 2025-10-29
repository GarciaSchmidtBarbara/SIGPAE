@extends('layouts.base')

@section('encabezado', 'Perfil')

@section('contenido')
    <div class="informacion">
        <p class="separador">Mi información personal</p>
        <div class="grid grid-cols-2 gap-4 my-2">
            <div>
                <p>Nombre</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
            <div>
                <p>Apellido</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
            <div>
                <p>Profesión</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
            <div>
                <p>Siglas</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
        </div>
    </div>
    <div class="informacion">
        <p class="separador">Credenciales</p>
        <div class="grid grid-cols-2 gap-4 my-2">
            <div>
                <p>Usuario</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
            <div class=" self-center">
                <a href="{{ route('perfil.editar') }}" class="links">Editar credenciales</a>
            </div>
            <div>
                <p>Mail registrado</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
            <div>
                <p>contraseña</p>
                <input type="text" placeholder="nombre" class="form-input">
            </div>
        </div>
    </div>
    <div class="bg-purple-500 flex justify-end mx-5 gap-4">
        <boton>Eliminar</boton>
        <boton>Guardar</boton>
    </div>
    
@endsection
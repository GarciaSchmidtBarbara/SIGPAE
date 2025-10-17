@extends('layouts.base')

@section('encabezado', 'Perfil')

@section('contenido')
    <div class="informacion">
        <p class="separador">Mi información personal</p>
        <div class="grid grid-cols-2">
            <div>
                <p>Nombre</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>Apellido</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>Profesión</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>Siglas</p>
                <p class="caja">texto</p>
            </div>
        </div>
    </div>
    <div class="informacion">
        <p class="separador">Credenciales</p>
        <div class="grid grid-cols-2">
            <div>
                <p>Usuario</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>editar</p>
                <button></button>
            </div>
            <div>
                <p>Mail registrado</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>contraseña</p>
                <p class="caja">texto</p>
            </div>
        </div>
    </div>
    <boton>Volver</boton>
@endsection
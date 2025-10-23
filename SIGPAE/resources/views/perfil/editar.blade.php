@extends('layouts.base')

@section('encabezado', 'Perfil')

@section('contenido')
    <div class="informacion">
        <p class="separador">Credenciales</p>
        <div class="">
            <div>
                <p>Mail registrado</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>Nueva contraseña</p>
                <p class="caja">texto</p>
                <p>Confirmar nueva contraseña</p>
                <p class="caja">texto</p>
            </div>
            <div>
                <p>Usuario</p>
                <p class="caja">texto</p>
            </div>
        </div>
    </div>
    <div>
        <boton>Guardar</boton>
        <a href="{{ route('perfil.principal') }}" class="links">Volver</a>

    </div>
    
@endsection
@extends('layouts.base')

@section('encabezado', 'Planes de acción')

@section('contenido')
    <div class="fila-componentes">
      <x-boton-aceptar>Crear</x-boton-aceptar>
      <boton>Tipo</boton>
      <boton>Estado</boton>
      <boton>Curso</boton>
      <boton>Buscar</boton>
    </div>
    <p>Aquí va la tabla de planes. REDEFINIR CON COMPONENTES BLADE</p>
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Destinatarios</th>
                <th>Responsables</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Aquí van las filas de la tabla -->
            <tr>
                <td>Abierto</td>
                <td>Institucional</td>
                <td>Escuela n°41</td>
                <td>PS. Juan Flores</td>
                <td><boton>Ver</boton> <boton>Editar</boton></td>
            </tr>
            <tr>
                <td>Abierto</td>
                <td>Individual</td>
                <td>Maria Pepe</td>
                <td>AS. Lucas Diaz</td>
                <td><boton>Ver</boton> <boton>Editar</boton></td>
            </tr>
            <!-- Más filas según sea necesario -->
        </tbody>
    </table>
    <boton>Volver</boton>
@endsection
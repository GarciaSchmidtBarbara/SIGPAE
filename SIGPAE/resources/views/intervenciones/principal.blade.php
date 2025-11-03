@extends('layouts.base')

@section('encabezado', 'Intervenciones')

@section('contenido')
    <div class="fila-componentes">
      <a class="btn-aceptar" href="#">Crear</a>
      <button class="btn-desplegable">Tipo</button>
      <button class="btn-desplegable">Estado</button>
      <button class="btn-desplegable">Curso</button>
      <button class="desplegable">Buscar</button>
    </div>
    <p>Aquí va la tabla de intervenciones.</p>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Alumno</th>
                <th>Profesional</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Aquí van las filas de la tabla -->
            <tr>
                <td>01/11/2025</td>
                <td>Espontánea</td>
                <td>Juan Pérez</td>
                <td>PS. María García</td>
                <td><boton>Ver</boton> <boton>Editar</boton></td>
            </tr>
            <!-- Más filas según sea necesario -->
        </tbody>
    </table>
    <boton>Volver</boton>
@endsection

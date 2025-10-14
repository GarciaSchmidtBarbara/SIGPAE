@extends('layouts.base')

@section('encabezado', 'nombre de la sección')

@section('contenido')
    <!-- contenido específico -->
    <p>Ejemplo de uso de componentes Blade</p>
    <x-boton-aceptar>Guardar</x-boton-aceptar>
    <x-boton-aceptar class="bg-danger">Eliminar mi cuenta</x-boton-aceptar>

    @php
    $intervenciones = ['Psicopedagógica', 'Social', 'Académica', 'Familiar'];
    @endphp

    <p>Selección múltiple:</p>
    <x-checkboxes :items="$intervenciones" name="tipo_intervencion" />

    <p>Selección única:</p>
    <x-opcion-unica :items="$intervenciones" name="tipo_intervencion_unica" />
@endsection

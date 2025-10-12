@extends('layouts.base')

@section('encabezado', 'Planes de acción')

@section('contenido')
    <!-- contenido específico -->
      <x-boton-aceptar>Guardar</x-boton-aceptar>
      <x-boton-aceptar class="bg-danger">Eliminar mi cuenta</x-boton-aceptar>

      @php
        $intervenciones = ['Psicopedagógica', 'Social', 'Académica', 'Familiar'];
      @endphp

        <x-checkboxes :items="$intervenciones" name="tipo_intervencion" />
        <p>Seleccione una única intervención:</p>
        <x-opcion-unica :items="$intervenciones" name="tipo_intervencion_unica" />
@endsection
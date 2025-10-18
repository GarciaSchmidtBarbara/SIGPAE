@extends('layouts.base')

@section('encabezado', 'Ejemplo de Componentes')

@section('contenido')
    <div class="space-y-8">
        <!-- Sección de botones -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Botones</h2>
            <div class="flex gap-4">
                <button class="btn-aceptar">Guardar</button>
                <button class="btn-eliminar">Eliminar mi cuenta</button>
            </div>
        </div>

        @php
        $intervenciones = ['Psicopedagógica', 'Social', 'Académica', 'Familiar'];
        @endphp

        <!-- Sección de checkboxes vertical -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección múltiple (Vertical)</h2>
            <x-checkboxes :items="$intervenciones" name="tipo_intervencion_vertical" layout="vertical" />
        </div>

        <!-- Sección de checkboxes horizontal -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección múltiple (Horizontal)</h2>
            <x-checkboxes :items="$intervenciones" name="tipo_intervencion_horizontal" layout="horizontal" />
        </div>

        <!-- Sección de radio buttons vertical -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección única (Vertical)</h2>
            <x-opcion-unica :items="$intervenciones" name="tipo_intervencion_unica_vertical" layout="vertical" />
        </div>

        <!-- Sección de radio buttons horizontal -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección única (Horizontal)</h2>
            <x-opcion-unica :items="$intervenciones" name="tipo_intervencion_unica_horizontal" layout="horizontal" />
        </div>
    </div>
@endsection

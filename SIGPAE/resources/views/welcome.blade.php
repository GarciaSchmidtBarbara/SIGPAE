@extends('layouts.base')

@section('encabezado', '')  <!--Encabezado de la pagina de bienvenida, si se quiere se pone algo, si no, no-->

@section('contenido')
    <div class="h-full flex items-end justify-end pb-6">
        <!-- Contenedor del calendario en esquina inferior derecha -->
        <div class="w-96">
            <div id="calendar" class="bg-white rounded-lg shadow-md p-3 text-sm"></div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/calendario.js'])
    @endpush
@endsection
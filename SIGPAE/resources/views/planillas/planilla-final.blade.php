@extends('layouts.base')

{{-- CAMBIO 1: Título --}}
@section('encabezado', 'Planilla Final')

@section('contenido')

    <form action="{{ route('planillas.planilla-final.store') }}" method="POST">
    @csrf

    <div class="max-w-[95%] mx-auto mt-6 px-2">
        <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200">

        
            <div class="flex flex-wrap items-center justify-between gap-6 mb-8">
                
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        {{-- Título visual --}}
                        <label class="font-bold text-xl text-gray-800">Planilla Final año</label>
                        <input type="number" name="anio" value="{{ date('Y') }}" class="border border-gray-400 rounded px-2 py-1 w-24 text-xl font-bold text-center">
                    </div>

                    <div class="flex items-center gap-2 ml-4">
                        <label class="font-bold text-gray-700">Fecha:</label>
                        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-gray-600">
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="font-bold text-gray-800">Equipo interdisciplinario: Escuela provincial N:</label>
                    <input type="text" name="escuela" class="border border-gray-400 rounded px-2 py-1 flex-1 md:w-48">
                </div>
            </div>

            <x-tabla-medial />

            {{-- BOTONERA --}}
            <div class="mt-10 pt-6 border-t border-gray-200">
                 <div class="fila-botones justify-between items-center">
                    <div class="flex gap-3">
                        <button type="button" class="btn-eliminar">Eliminar</button>
                        <button type="button" class="btn-gris-variantes" onclick="window.print()">Vista Previa</button>
                        <button type="button" class="btn-aceptar">Descargar</button>
                        <button type="submit" class="btn-aceptar">Guardar</button>
                        <a href="#" class="btn-volver">Volver</a>
                    </div>  
                 </div>
            </div>

        </div>
    </div>
    </form>
@endsection
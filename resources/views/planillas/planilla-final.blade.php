@extends('layouts.base')

{{-- Título de la página --}}
@section('encabezado', 'Planilla Final')

@section('contenido')
@php
    // si viene desde ver / editar / descargar
    $soloLectura = $soloLectura ?? false;
    $esEdicion   = isset($planilla);

    // Datos guardados (si existe la planilla)
    $datos = $esEdicion ? ($planilla->datos_planilla ?? []) : [];

    $anioValue     = old('anio',     $datos['anio']     ?? date('Y'));
    $fechaValue    = old('fecha',    $datos['fecha']    ?? date('Y-m-d'));
    $escuelaValue  = old('escuela',  $datos['escuela']  ?? '');

    // Por si en algún momento cambian el nombre de la clave
    $filasIniciales = $datos['tabla_final']
        ?? $datos['tabla_medial']
        ?? [];
@endphp

<form
    method="POST"
    action="{{ $esEdicion
                ? route('planillas.actualizar', $planilla->id_planilla)
                : route('planillas.planilla-final.store') }}"
>
    @csrf
    @if($esEdicion)
        @method('PUT')
    @endif

    <div class="max-w-[95%] mx-auto mt-6 px-2">
        <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200">

            {{-- Año, Fecha y Escuela --}}
            <div class="flex flex-wrap items-center justify-between gap-6 mb-8">

                <div class="flex items-center gap-4">
                    {{-- Año --}}
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-xl text-gray-800">
                            Planilla Final año
                        </label>
                        <input
                            type="number"
                            name="anio"
                            value="{{ $anioValue }}"
                            class="border border-gray-400 rounded px-2 py-1 w-24 text-xl font-bold text-center"
                            {{ $soloLectura ? 'readonly disabled' : '' }}
                        >
                    </div>

                    {{-- Fecha --}}
                    <div class="flex items-left gap-2 ml-4">
                        <label class="font-bold text-gray-700">Fecha:</label>
                        <input
                            type="date"
                            name="fecha"
                            value="{{ $fechaValue }}"
                            class="border border-gray-300 rounded px-2 py-1 text-gray-600"
                            {{ $soloLectura ? 'readonly disabled' : '' }}
                        >
                    </div>
                </div>

                {{-- Escuela --}}
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label class="font-bold text-gray-800">
                        Equipo interdisciplinario: Escuela provincial N:
                    </label>
                    <input
                        type="text"
                        name="escuela"
                        value="{{ $escuelaValue }}"
                        class="border border-gray-400 rounded px-2 py-1 flex-1 md:w-48"
                        {{ $soloLectura ? 'readonly disabled' : '' }}
                    >
                </div>
            </div>

            {{-- TABLA (usa el mismo componente que la Medial) --}}
            <x-tabla-medial
                :soloLectura="$soloLectura"
                :filasIniciales="$filasIniciales"
            />

            {{-- BOTONERA --}}
            <div class="mt-10 pt-6 border-t border-gray-200">
                <div class="fila-botones justify-between items-center">
                    <div class="flex gap-3">
                        @unless($soloLectura)
                            <button type="button" class="btn-eliminar">Eliminar</button>
                            <button type="submit" class="btn-aceptar">Guardar</button>
                        @endunless

                        <button
                            type="button"
                            class="btn-gris-variantes"
                            onclick="window.print()"
                        >
                            Vista Previa
                        </button>

                        <button
                            type="button"
                            class="btn-aceptar"
                            onclick="window.print()"
                        >
                            Descargar
                        </button>

                        <a href="{{ route('planillas.principal') }}" class="btn-volver">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

@if (!empty($autoImprimir))
    <script>
        window.addEventListener('load', () => window.print());
    </script>
@endif
@endsection

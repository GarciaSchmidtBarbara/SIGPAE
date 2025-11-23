@extends('layouts.base')

@section('encabezado', 'Todas las Intervenciones')

@section('contenido')

    {{-- Mensajes de estado --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

<div class="p-6">
    <form id="form-intervencion" method="GET" action="{{ route('intervenciones.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        <a class="btn-aceptar" href="{{ route('intervenciones.crear-editar') }}">Crear Intervención</a>
        
        <select name="tipo_intervencion" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los tipos</option>
            @foreach($tiposIntervencion as $tipo)
                <option value="{{ $tipo }}" {{ request('tipo_intervencion') === $tipo ? 'selected' : '' }}>
                    {{ $tipo }}
                </option>
            @endforeach
        </select>

        <input name="nombre" placeholder="Nombre/DNI" class="border px-2 py-1 rounded w-1/5">

        <select name="aula" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($cursos as $curso)
                <option value="{{ $curso->descripcion }}" {{ request('aula') === $curso->descripcion ? 'selected' : '' }}>
                    {{ $curso->descripcion }}
                </option>
            @endforeach
        </select>

        <p>Desde</p>
        <input type="date" name="fecha_desde" class="border px-2 py-1 rounded" value="{{ request('fecha_desde') }}">
        <p>Hasta</p>
        <input type="date" name="fecha_hasta" class="border px-2 py-1 rounded" value="{{ request('fecha_hasta') }}">

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('alumnos.principal') }}" >Limpiar</a>
    </form>

    @php
        $accionesPorFila = function ($fila) {
            $activo = data_get($fila, 'intervencion.activo');
            $ruta = route('intervenciones.cambiarActivo', data_get($fila, 'id_intervencion'));
            return view('components.boton-estado', [
                'activo' => $activo,
                'route' => $ruta
            ])->render();
        };
    @endphp
    

    <x-tabla-dinamica 
        :columnas="[
            ['key' => 'intervencion.fecha', 'label' => 'Fecha'],
            ['key' => 'intervencion.tipo', 'label' => 'Tipo'],
            ['key' => 'persona.dni', 'label' => 'Destinatarios'],
            ['key' => 'profesional', 'label' => 'Intervinientes'],
        ]"
        :filas="$intervenciones"
        :acciones="fn($fila) => view('components.boton-estado', [
            'activo' => data_get($fila, 'intervencion.activo'),
            'route' => route('intervenciones.cambiarActivo', data_get($fila, 'id_intervencion'))
        ])->render()"

        idCampo="id_intervencion"
        :filaEnlace="fn($fila) => route('intervenciones.editar', data_get($fila, 'id_intervencion'))"
    >
        <x-slot:accionesPorFila>
            @php
                // función anónima que recibirá $fila
            @endphp
            @once
                @php
                    $accionesPorFila = function ($fila) {
                        $activo = data_get($fila, 'intervencion.activo');
                        $ruta = route('intervenciones.cambiarActivo', data_get($fila, 'id_intervencion'));
                        return view('components.boton-estado', [
                            'activo' => $activo,
                            'route' => $ruta
                        ])->render();
                    };
                @endphp
            @endonce
        </x-slot:accionesPorFila>
    </x-tabla-dinamica>

    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection

@extends('layouts.base')

@section('encabezado', 'Todas las Intervenciones')

{{--ESTE H2 COLOCA EL TITULO AL DOCUMENTO--}}
<h2 class="page-title-print" style="display: none;">@yield('encabezado') </h2>

@section('contenido')

<div class="p-6">    
    <form id="form-intervencion" method="GET" action="{{ route('intervenciones.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        
        <a class="btn-aceptar" href="{{ route('intervenciones.crear') }}">Crear Intervención</a>
        
        <select name="tipo_intervencion" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los tipos</option>
            @foreach($tiposIntervencion as $tipo)
                <option value="{{ $tipo }}" {{ request('tipo_intervencion') === $tipo ? 'selected' : '' }}>
                    {{ $tipo }}
                </option>
            @endforeach
        </select>

        <input name="nombre" value="{{ request('nombre') }}" placeholder="Nombre/DNI" class="border px-2 py-1 rounded w-1/5">

        <select name="aula" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($aulas as $curso)
                <option value="{{ $curso->id }}" {{ request('aula') == $curso->id ? 'selected' : '' }}>
                    {{ $curso->descripcion }}
                </option>
            @endforeach
        </select>

        <p>Desde</p>
        <input type="date" name="fecha_desde" class="border px-2 py-1 rounded" value="{{ request('fecha_desde') }}">
        <p>Hasta</p>
        <input type="date" name="fecha_hasta" class="border px-2 py-1 rounded" value="{{ request('fecha_hasta') }}">

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('intervenciones.principal') }}" >Limpiar</a>   
    </form>

    {{--ENVOLVER LA TABLA A IMPRIMIR--}}
    <div class="data-table-to-print">
        @php
            $columnasIntervenciones = [
                ['key' => 'tipo_intervencion', 'label' => 'Tipo', 'formatter' => function($v) {
                    $colors = ['PROGRAMADA' => 'bg-blue-100 text-blue-700 border-blue-200', 'ESPONTANEA' => 'bg-green-100 text-green-700 border-green-200'];
                    $val = $v instanceof \BackedEnum ? $v->value : (string)$v;
                    $color = $colors[$val] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $color . '">' . ucfirst(strtolower($val)) . '</span>';
                }],
                ['key' => 'fecha_hora_intervencion', 'label' => 'Fecha y Hora'],
                ['key' => 'alumnos', 'label' => 'Destinatarios'],
                ['key' => 'profesionales', 'label' => 'Intervinientes'],
            ];
        @endphp
        <x-tabla-dinamica 
            :columnas="$columnasIntervenciones"
            :filas="$intervenciones"
            :acciones="fn($fila) => view('components.boton-eliminar', [
                'route' => route('intervenciones.eliminar', $fila['id_intervencion']),
                'message' => '¿Desea eliminar esta intervención?'
            ])->render()"
            idCampo="id_intervencion"
            class="tabla-imprimir" {{--PONERLE LA CLASE A LA TABLA A IMPRIMIR--}}
            :filaEnlace="fn($fila) => route('intervenciones.editar', data_get($fila, 'id_intervencion'))"
        >
            <x-slot:accionesPorFila>
                @once
                    @php
                        $accionesPorFila = function ($fila) {
                            $activo = data_get($fila, 'activo');
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
    </div>

    <div class="fila-botones mt-8">
        {{--ESTE BOTON BUSCA LA TABLA A IMPRIMIR--}}
        <button type="button" class="btn-aceptar btn-print-table no-print">Imprimir listado</button> 
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>

@endsection


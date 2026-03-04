@extends('layouts.base')

@section('encabezado', 'Todas las Intervenciones')
<h2 class="page-title-print" style="display: none;">@yield('encabezado') </h2>

@section('contenido')

<div class="px-4 py-6 md:p-6">    
    <form id="form-intervencion" x-ref="filtroForm" method="GET" action="{{ route('intervenciones.principal') }}" class="flex flex-wrap items-center gap-3 mb-6">    

        <a class="btn-aceptar shrink-0" href="{{ route('intervenciones.crear') }}">Crear Intervención</a>

        <select name="tipo_intervencion" class="form-input w-auto"
            @change="$refs.filtroForm.submit()">
            <option value="">Todos los tipos</option>
            @foreach($tiposIntervencion as $tipo)
                <option value="{{ $tipo }}" {{ request('tipo_intervencion') === $tipo ? 'selected' : '' }}>
                    {{ $tipo }}
                </option>
            @endforeach
        </select>

        <input name="nombre" value="{{ request('nombre') }}" placeholder="Nombre/DNI" class="form-input w-36"
            @input.debounce.700ms="$refs.filtroForm.submit()">

        <select name="aula" class="form-input w-auto"
            @change="$refs.filtroForm.submit()">
            <option value="">Todos los cursos</option>
            @foreach($aulas as $curso)
                <option value="{{ $curso->id_aula }}" {{ request('aula') == $curso->id_aula ? 'selected' : '' }}>
                    {{ $curso->descripcion }}
                </option>
            @endforeach
        </select>

        <div class="flex items-center gap-1 shrink-0">
            <span class="text-sm text-gray-600 whitespace-nowrap">Desde</span>
            <input type="date" name="fecha_desde" class="form-input w-auto" value="{{ request('fecha_desde') }}"
                @change="$refs.filtroForm.submit()">
        </div>

        <div class="flex items-center gap-1 shrink-0">
            <span class="text-sm text-gray-600 whitespace-nowrap">Hasta</span>
            <input type="date" name="fecha_hasta" class="form-input w-auto" value="{{ request('fecha_hasta') }}"
                @change="$refs.filtroForm.submit()">
        </div>

        <a class="btn-aceptar shrink-0" href="{{ route('intervenciones.principal') }}">Limpiar</a>
    </form>

    {{--ENVOLVER LA TABLA A IMPRIMIR--}}
    <div class="data-table-to-print bg-white rounded-xl shadow-sm">
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
        <button type="button" class="btn-aceptar btn-print-table no-print">Imprimir listado</button> 
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>

@endsection


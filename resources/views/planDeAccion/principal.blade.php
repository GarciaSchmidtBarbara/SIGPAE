@extends('layouts.base')

@section('encabezado', 'Planes de acción')
<h2 class="page-title-print" style="display: none;">@yield('encabezado') </h2>

@section('contenido')

<div class="p-6">
    <form id="form-plan" method="GET" action="{{ route('planDeAccion.principal') }}" 
        class="flex gap-2 mb-6 flex-nowrap items-center justify-between">    

        <div class="flex gap-3 items-center">
            <a class="btn-aceptar" href="{{ route('planDeAccion.iniciar-creacion') }}">Crear Plan de Acción</a>

            <a href="{{ route('planDeAccion.papelera') }}"
                class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-1 ml-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Ver Papelera
            </a>
        </div>

        <input name="alumno" placeholder="Alumno (Nombre/DNI)" class="border px-2 py-1 rounded w-1/5" value="{{ request('alumno') }}">

        <select name="tipo" class="border px-2 py-1 rounded">
            <option value="" {{ request('tipo') === null ? 'selected' : '' }}>Todos los Tipos</option>
            @foreach($tipos as $tipo)
                <option value="{{ $tipo }}" {{ request('tipo') === $tipo ? 'selected' : '' }}>
                    {{ ucfirst(strtolower($tipo)) }}
                </option>
            @endforeach
        </select>

        <select name="estado" class="border px-2 py-1 rounded w-1/5">
            <option value="" {{ request('estado')  === null ? 'selected' : '' }}>Todos</option>
            <option value="activos" {{ request('estado', 'activos') === 'activos' ? 'selected' : '' }}>Abiertos</option>
            <option value="inactivos" {{ request('estado', 'activos')  === 'inactivos' ? 'selected' : '' }}>Cerrados</option>
        </select>

        <select name="curso" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($aulas as $aula)
                <option value="{{ $aula->id }}" {{ (int)request('curso') === $aula->id ? 'selected' : '' }}>
                    {{ $aula->descripcion }}
                </option>
            @endforeach
        </select>
        

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('planDeAccion.principal') }}" >Limpiar</a>
    </form>

    @php
    $columnas=[
            [
                'key' => 'tipo_plan',
                'label' => 'Tipo',
                'formatter' => function($v) {
                    $colors = ['INDIVIDUAL' => 'bg-blue-100 text-blue-700 border-blue-200', 'GRUPAL' => 'bg-green-100 text-green-700 border-green-200', 'INSTITUCIONAL' => 'bg-purple-100 text-purple-700 border-purple-200'];
                    $val = $v instanceof \BackedEnum ? $v->value : (string)$v;
                    $color = $colors[$val] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $color . '">' . ucfirst(strtolower($val)) . '</span>';
                },
            ],
            [
                'key' => 'estado_plan',
                'label' => 'Estado',
                'formatter' => fn($v) => ucfirst(strtolower($v->value)),
            ],
            [
                'key' => 'destinatarios',
                'label' => 'Destinatarios',
                'formatter' => function ($v, $plan) {
                    $destinatarios = collect();

                    if ($plan->alumnos->isNotEmpty()) {
                        $destinatarios->push(...$plan->alumnos->map(fn($a) => $a->persona->apellido . ', ' . $a->persona->nombre));
                    }

                    if ($plan->tipo_plan->value === 'GRUPAL' && $plan->aulas->isNotEmpty()) {
                        $destinatarios->push(...$plan->aulas->map(fn($aula) => 'Aula: ' . $aula->descripcion));
                    }

                    return $destinatarios->isNotEmpty() ? $destinatarios->implode(', ') : '—';
                }
            ],
            [
                'key' => 'responsables',
                'label' => 'Responsables',
                'formatter' => function ($v, $plan) {
                    $responsables = collect();

                    if ($plan->profesionalGenerador?->persona) {
                        $p = $plan->profesionalGenerador->persona;
                        $responsables->push("<strong>{$p->apellido}, {$p->nombre}</strong>");
                    }

                    foreach ($plan->profesionalesParticipantes as $prof) {
                        if ($prof->id_profesional !== $plan->fk_id_profesional_generador && $prof->persona) {
                            $p = $prof->persona;
                            $responsables->push("{$p->apellido}, {$p->nombre}");
                        }
                    }

                    return $responsables->isNotEmpty() ? $responsables->unique()->implode('<br>') : '—';
                }

            ],
        ]
    @endphp
    {{-- Lógica de la Tabla Dinámica --}}
<div class="data-table-to-print">
    <x-tabla-dinamica 
        
        :filas="$planesDeAccion"
        :columnas="$columnas"
        idCampo="id_plan_de_accion"
        class="tabla-imprimir"
        :filaEnlace="fn($plan) => route('planDeAccion.iniciar-edicion', $plan->id_plan_de_accion)"

        :acciones="fn($plan) => 
            view('components.boton-estado', [
                'activo' => $plan->activo,
                'route'  => route('planDeAccion.cambiarActivo', $plan->id_plan_de_accion),
                'text_activo' => 'Cerrar', 
                'text_inactivo' => 'Abrir',
            ])->render()
            . ' ' .
            view('components.boton-eliminar', [
                'route' => route('planDeAccion.eliminar', ['id' => $plan->id_plan_de_accion]),
                'message' => '¿Enviar a la papelera?'
            ])->render()"
        />
</div>

    <div class="fila-botones mt-8">
        <button type="button" class="btn-aceptar btn-print-table no-print">Imprimir listado</button>
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection
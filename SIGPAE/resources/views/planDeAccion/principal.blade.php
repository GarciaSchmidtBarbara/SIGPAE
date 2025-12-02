@extends('layouts.base')

@section('encabezado', 'Planes de acción')

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
    <form id="form-plan" method="GET" action="{{ route('planDeAccion.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        <a class="btn-aceptar" href="{{ route('planDeAccion.iniciar-creacion') }}">Crear Plan de Acción</a>

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
                'key' => 'estado_plan',
                'label' => 'Estado',
                'formatter' => fn($v) => ucfirst(strtolower($v->value)),
            ],
            [
                'key' => 'tipo_plan',
                'label' => 'Tipo',
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
    <x-tabla-dinamica 
        
        :filas="$planesDeAccion"
        :columnas="$columnas"
        idCampo="id_plan_de_accion"

        :filaEnlace="fn($plan) => route('planDeAccion.iniciar-edicion', $plan->id_plan_de_accion)"

        :acciones="fn($plan) => view('components.boton-estado', [
            'activo' => $plan->activo,
            'route'  => route('planDeAccion.cambiarActivo', $plan->id_plan_de_accion),
            'text_activo' => 'Cerrar', 
            'text_inactivo' => 'Abrir',
        ])->render()"
    />


    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection
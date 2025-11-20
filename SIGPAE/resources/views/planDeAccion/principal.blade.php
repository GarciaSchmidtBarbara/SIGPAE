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

    {{-- Lógica de la Tabla Dinámica --}}
    <x-tabla-dinamica 
        :columnas="[
            [
                'key' => 'estado_plan',
                'label' => 'Estado',
                'formatter' => fn($v) => ucfirst(strtolower($v)),
            ],
            [
                'key' => 'tipo_plan',
                'label' => 'Tipo',
                'formatter' => fn($v) => ucfirst(strtolower($v)),
            ],
            [
                'key' => 'destinatarios',
                'label' => 'Destinatarios',
                'formatter' => function ($valor, $plan) {
                    $tipo = $plan->tipo_plan->value; // Asumo que tipo_plan es un Enum que puedes acceder
                    
                    if ($tipo === 'INDIVIDUAL') {
                        $alumno = $plan->alumnos->first();
                        return $alumno 
                            ? $alumno->persona->apellido . ', ' . $alumno->persona->nombre
                            : 'N/A';
                    }

                    if ($tipo === 'GRUPAL') {
                        $aula = $plan->aulas->first();
                        return $aula ? $aula->descripcion : 'Grupo';
                    }

                    return 'Institucional';
                }
            ],
            [
                'key' => 'responsables',
                'label' => 'Responsables',
                'formatter' => function ($valor, $plan) {
                    $nombres = [];

                    if ($plan->profesionalGenerador?->persona) {
                        $nombres[] = $plan->profesionalGenerador->persona->apellido;
                    }
                    foreach ($plan->profesionalesParticipantes as $prof) {
                        if ($prof->persona) {
                            $nombres[] = $prof->persona->apellido;
                        }
                    }

                    $nombres = array_unique($nombres);

                    return count($nombres) > 2
                        ? implode(', ', array_slice($nombres, 0, 2)) . '...'
                        : implode(', ', $nombres);
                }
            ],
        ]"

        :filas="$planesDeAccion"
        idCampo="id_plan_de_accion"

        :filaEnlace="fn($fila) => route('planDeAccion.iniciar-edicion', $fila->id_plan_de_accion)"

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
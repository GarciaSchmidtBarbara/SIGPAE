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
                    $tipo = $plan->tipo_plan->value; 
                    
                    if ($tipo === 'INDIVIDUAL' || $tipo === 'GRUPAL') {
                        $destinatarios = collect();
                        
                        // 1. Alumnos (para Individual y Grupal)
                        if ($plan->alumnos->isNotEmpty()) {
                            $alumnos_nombres = $plan->alumnos
                                ->map(fn($alumno) => $alumno->persona->apellido . ', ' . $alumno->persona->nombre)
                                ->all();
                            $destinatarios = $destinatarios->merge($alumnos_nombres);
                        }
                        
                        // 2. Aulas (solo para Grupal)
                        if ($tipo === 'GRUPAL' && $plan->aulas->isNotEmpty()) {
                            $aulas_descripcion = $plan->aulas
                                ->map(fn($aula) => 'Aula: ' . $aula->descripcion)
                                ->all();
                            $destinatarios = $destinatarios->merge($aulas_descripcion);
                        }
                        
                        return $destinatarios->isNotEmpty() 
                            ? $destinatarios->implode('<br>') 
                            : 'N/A';
                    }

                    return 'Institucional';
                }
            ],
            [
                'key' => 'responsables',
                'label' => 'Responsables',
                'formatter' => function ($valor, $plan) {
                    $responsables = collect();

                    if ($plan->profesionalGenerador?->persona) {
                        $p = $plan->profesionalGenerador->persona;
                        $responsables->push([
                            'nombre_completo' => $p->apellido . ', ' . $p->nombre,
                            'es_generador' => true,
                        ]);
                    }

                    // otros Participantes
                    foreach ($plan->profesionalesParticipantes as $prof) {
                        // Evitar duplicar el generador si está listado como participante
                        if ($prof->id_profesional !== $plan->fk_id_profesional_generador && $prof->persona) {
                             $p = $prof->persona;
                             $responsables->push([
                                'nombre_completo' => $p->apellido . ', ' . $p->nombre,
                                'es_generador' => false,
                            ]);
                        }
                    }

                    // Formatear la lista final
                    if ($responsables->isEmpty()) {
                        return '—';
                    }

                    return $responsables
                        ->unique('nombre_completo')
                        ->map(fn($r) => $r['es_generador'] ? "<strong>{$r['nombre_completo']} (Gen.)</strong>" : $r['nombre_completo'])->implode('<br>');
                }
            ],
        ]
    @endphp
    {{-- Lógica de la Tabla Dinámica --}}
    <x-tabla-dinamica 
        
        :filas="$planesDeAccion"
        :columnas="$columnas"
        idCampo="id_plan_de_accion"


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
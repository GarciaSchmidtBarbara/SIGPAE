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

        <select name="tipo" class="border px-2 py-1 rounded">
            <option value="" {{ request('tipo') === null ? 'selected' : '' }}>Tipo</option>
            @foreach(\App\Enums\TipoPlan::cases() as $tipo)
                <option value="{{ $tipo->value }}" {{ request('tipo') === $tipo->value ? 'selected' : '' }}>
                    {{ ucfirst(strtolower($tipo->value)) }}
                </option>
            @endforeach
        </select>

        <select name="estado" class="border px-2 py-1 rounded">
            <option value="" {{ request('estado') === null ? 'selected' : '' }}>Estado</option>
            @foreach(\App\Enums\EstadoPlan::cases() as $estado)
                <option value="{{ $estado->value }}" {{ request('estado') === $estado->value ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', ucfirst(strtolower($estado->value))) }}
                </option>
            @endforeach
        </select>

        <select name="curso" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($aulas as $aula)
                <option value="{{ $aula->id }}" {{ request('curso') === $aula->id ? 'selected' : '' }}>
                    {{ $aula->descripcion }}
                </option>
            @endforeach
        </select>
        

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('planDeAccion.principal') }}" >Limpiar</a>
    </form>

    {{-- Lógica de la Tabla Dinámica --}}
    @php
        // Formateadores para la tabla
        $formatters = [
            'estado_plan' => fn($valor) => str_replace('_', ' ', ucfirst(strtolower($valor))),
            'tipo_plan' => fn($valor) => ucfirst(strtolower($valor)),
        ];

        // Función para obtener los destinatarios (Alumnos o Aulas)
        $destinatariosFormatter = function (\App\Models\PlanDeAccion $plan) {
            if ($plan->tipo_plan->value === \App\Enums\TipoPlan::INDIVIDUAL->value) {
                // Individual: mostrar el primer alumno
                $alumno = $plan->alumnos->first();
                return $alumno ? $alumno->persona->nombre . ' ' . $alumno->persona->apellido : 'N/A';
            } elseif ($plan->tipo_plan->value === \App\Enums\TipoPlan::GRUPAL->value) {
                // Grupal: mostrar el curso/aula
                $aula = $plan->aulas->first();
                return $aula ? $aula->descripcion : 'Grupo';
            } else {
                return 'Institucional';
            }
        };

        // Función para obtener los responsables (Profesionales)
        $responsablesFormatter = function (\App\Models\PlanDeAccion $plan) {
            $nombres = [];
            // Profesional Generador (siempre hay uno)
            if ($plan->profesionalGenerador) {
                $nombres[] = $plan->profesionalGenerador->persona->apellido;
            }
            // Profesionales Participantes
            foreach ($plan->profesionalesParticipantes as $profesional) {
                $nombres[] = $profesional->persona->apellido;
            }

            // Devolver los apellidos únicos, limitando a 2 y añadiendo "..." si hay más
            $responsablesUnicos = array_unique($nombres);
            return count($responsablesUnicos) > 2 
                ? implode(', ', array_slice($responsablesUnicos, 0, 2)) . '...' 
                : implode(', ', $responsablesUnicos);
        };
    @endphp
    

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
                    $tipo = $plan->tipo_plan->value;

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

                    // Profesional generador
                    if ($plan->profesionalGenerador?->persona) {
                        $nombres[] = $plan->profesionalGenerador->persona->apellido;
                    }

                    // Participantes
                    foreach ($plan->profesionalesParticipantes as $prof) {
                        if ($prof->persona) {
                            $nombres[] = $prof->persona->apellido;
                        }
                    }

                    // Únicos + límite 2
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
            'route'  => route('planDeAccion.cambiarActivo', $plan->id_plan_de_accion)
        ])->render()"
    />


    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection
@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Intervención' : 'Crear Intervención')

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
@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@php
    $esEdicion = isset($modo) && $modo === 'editar' && isset($intervencion);
    $cerrado = $esEdicion ? ($intervencion->activo === false) : false;

    // helper para valores viejos del modelo
    $oldOr = fn($field, $fallback = null) => old($field, $fallback);

    // CREACIÓN → genera alumnosJson para Alpine
    if (!$esEdicion) {
        $alumnosJson = collect($alumnos)->mapWithKeys(function ($al) {
            $persona = $al->persona;
            return [
                $al->id_alumno => [
                    'id' => $al->id_alumno,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'dni' => $persona->dni,
                    'curso'   => $al->aula?->descripcion,
                    'aula_id' => $al->fk_id_aula,
                ]
            ];
        });

        $profesionalesJson = collect($profesionales)->mapWithKeys(function ($prof) {
            $persona = $prof->persona;
            return [
                $prof->id_profesional => [
                    'id' => $prof->id_profesional,
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido,
                    'profesion' => $prof->profesion ?? 'N/A',
                ]
            ];
        });
    }

    // Asegurar que $profesionalesJson exista también en modo edición
    if (!isset($profesionalesJson)) {
        $profesionalesJson = collect($profesionales)->mapWithKeys(function ($prof) {
            $persona = $prof->persona;
            return [
                $prof->id_profesional => [
                    'id' => $prof->id_profesional,
                    'nombre' => $persona->nombre ?? null,
                    'apellido' => $persona->apellido ?? null,
                    'profesion' => $prof->profesion ?? 'N/A',
                ]
            ];
        });
    }

    // Profesional generador para mostrar en la vista
    $profesionalGenerador = null;
    if ($esEdicion && isset($intervencion->profesionalGenerador) && $intervencion->profesionalGenerador?->persona) {
        $pg = $intervencion->profesionalGenerador;
        $profesionalGenerador = trim(($pg->persona->apellido ?? '') . ', ' . ($pg->persona->nombre ?? ''));
    } else {
        $currentProfId = old('fk_id_profesional_generador', auth()->user()->id_profesional ?? null);
        if ($currentProfId) {
            $found = collect($profesionales)->firstWhere('id_profesional', $currentProfId);
            if ($found && isset($found->persona)) {
                $profesionalGenerador = trim(($found->persona->apellido ?? '') . ', ' . ($found->persona->nombre ?? ''));
            }
        }
    }
@endphp


<div class="p-6">
    <div class="mt-4 my-4 flex justify-between items-center">
        <div class="text-sm text-red-600 min-h-[1.5rem]">
            @if($esEdicion && $cerrado)
                <p>Esta intervención está cerrada. No se permiten modificaciones.</p>
            @endif
        </div>

        <div class="flex justify-end space-x-4">
            @if($esEdicion)
                <x-boton-estado 
                    :activo="$intervencion->activo" 
                    :route="route('intervenciones.cambiarActivo', $intervencion->id_intervencion)"
                    :text_activo="'Cerrar'"
                    :text_inactivo="'Abrir'"
                />
                @if($intervencion->planDeAccion)
                    <a class="btn-aceptar" href="{{ route('planDeAccion.iniciar-edicion', $intervencion->planDeAccion->id_plan_de_accion) }}">
                        Ver Plan Vinculado
                    </a>
                @endif
            @endif
            <a class="btn-volver" href="{{ url()->previous() }}">Volver</a>
            
        </div>
    </div>

    {{-- Alpine para controlar secciones por tipo --}}
    <div x-data="{
        tipoSeleccionado: '{{ old('tipo_intervencion', $esEdicion ? ($intervencion->tipo_intervencion ?? '') : '') }}',
        alumnoData: {{ $alumnosJson->toJson() }},
    }">

        <form method="POST" action="{{ $esEdicion 
                ? route('intervenciones.actualizar', $intervencion->id_intervencion)
                : route('intervenciones.guardar') 
            }}">
            @csrf

            {{-- Cuando se edita: método PUT --}}
            @if($esEdicion)
                @method('PUT')
            @endif

            {{-- Asegurar que el generador queda presente (fallback al usuario autenticado) --}}
            <input type="hidden" name="fk_id_profesional_generador" 
                value="{{ old('fk_id_profesional_generador', auth()->user()->id_profesional ?? auth()->id()) }}">

            <fieldset {{ $cerrado ? 'disabled' : '' }}>
                {{-- DATOS DE LA INTERVENCION --}}
                <div class="space-y-6 mb-6">
                    <p class="separador">Datos de la intervención</p>
                    
                    {{-- Fecha, hora y lugar --}}
                    <div class="flex space-x-4">
                        <div class="flex flex-col w-1/4">
                            <x-campo-requerido text="Fecha" required />
                            <input type="date" name="fecha_hora_intervencion" value="{{ old('fecha_hora_intervencion', $esEdicion ? optional($intervencion->fecha_hora_intervencion)->format('Y-m-d') : '') }}" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="flex flex-col w-1/4">
                            <x-campo-requerido text="Hora" required />
                            <input type="time" name="hora_intervencion" value="{{ old('hora_intervencion', $esEdicion ? optional($intervencion->fecha_hora_intervencion)->format('H:i') : '') }}" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="flex flex-col w-1/3">
                            <x-campo-requerido text="Lugar" required />
                            <input name="lugar" value="{{ old('lugar', $esEdicion ? $intervencion->lugar : '') }}" placeholder="Lugar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        {{-- Selector de plan--}}
                        <div class="selector-box" style="width: 50%;">
                            <label class="text-sm font-medium">Seleccionar Plan de Acción</label>
                            <select name="fk_id_plan_de_accion" x-model="planSeleccionado" @change="seleccionarPlan()" class="border px-2 py-1 rounded w-full">
                                <option value="">-- Seleccionar plan --</option>
                                @foreach($planes as $plan)
                                    <option value="{{ $plan->id_plan_de_accion }}" {{ old('fk_id_plan_de_accion', $esEdicion ? $intervencion->fk_id_plan_de_accion : '') == $plan->id_plan_de_accion ? 'selected' : '' }}>
                                        {{ $plan->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @php
                            $tipoItems = array_map(fn($t) => $t->value, \App\Enums\TipoIntervencion::cases());
                            $seleccionTipo = old('tipo_intervencion', $esEdicion ? ($intervencion->tipo_intervencion ?? '') : '');
                        @endphp

                        @if($esEdicion)
                            <p class="font-semibold text-gray-700">{{ $seleccionTipo }}</p>
                            <input type="hidden" name="tipo_intervencion" value="{{ $seleccionTipo }}">
                        @else
                            <x-opcion-unica
                                :items="$tipoItems"
                                name="tipo_intervencion"
                                layout="horizontal"
                                :seleccionTipo="$seleccionTipo"
                                x-model="tipoSeleccionado"
                            />
                        @endif
                    </div>
                </div>


                {{-- DESTINATARIOS --}}
                <div id="destinatarios" 
                x-data="datosPersonas({ alumnoData: @js($alumnosJson), alumnosIniciales: @js($alumnosSeleccionados ?? []) })"> 
                    <div class="space-y-6 mb-6">
                        <p class="separador">Destinatarios</p>
                        <div class="selectors-row">
                            {{-- Selector de alumno--}}
                            <div class="selector-box" style="width: 35%;">
                                <label class="text-sm font-medium">Seleccionar alumno</label>
                                <select x-model="alumnoSeleccionado" @change="agregarAlumno()">
                                    <option value="">-- Seleccionar alumno --</option>
                                    @foreach($alumnos as $al)
                                        <option value="{{ $al->id_alumno }}">
                                            {{ $al->persona->nombre }} {{ $al->persona->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Selección de aula --}}
                            <div class="selector-box" style="width: 20%;">
                                <label class="text-sm font-medium">Aula</label>
                                <select x-model="aulaSeleccionada" @change="agregarAula()">
                                    <option value="">-- Seleccionar aula --</option>
                                    @foreach($aulas as $a)
                                        <option value="{{ $a->id_aula }}">
                                            {{ $a->curso }}°{{ $a->division }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- TABLA DINÁMICA DE ALUMNOS SELECCIONADOS (Reemplaza a x-tabla-dinamica) --}}
                        <div class="mt-6">
                            <h3 class="font-medium text-base text-gray-700 mb-2">Alumnos Seleccionados</h3>
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>NOMBRE</th>
                                        <th>APELLIDO</th>
                                        <th>DNI</th>
                                        <th>CURSO</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="al in alumnosSeleccionados" :key="al.id">
                                        <tr>
                                            <td x-text="al.nombre"></td>
                                            <td x-text="al.apellido"></td>
                                            <td x-text="al.dni"></td>
                                            <td x-text="al.curso"></td>
                                            <td>
                                                <button type="button" @click="eliminarAlumno(al.id)" type="button" class="text-gray-400 hover:text-red-600 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    <tr x-show="alumnosSeleccionados.length === 0">
                                        <td colspan="5" class="text-center">No hay alumnos seleccionados.</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                        
                        {{-- Inputs Ocultos (Mantener) --}}
                        <template x-for="al in alumnosSeleccionados" :key="al.id">
                            <input type="hidden" name="alumnos[]" :value="al.id">
                        </template>

                    </div>
                </div>


                {{-- RESPONSABLES  --}}
                <div id="responsables2" 
                x-data="datosPersonas({ profesionalesData: @js($profesionalesJson), profesionalesIniciales: @js($profesionalesSeleccionados ?? []) })" >
                    <div class="space-y-6 mb-6">
                        <p class="separador">Asistentes</p>

                        {{-- Mostrar profesional generador--}}
                        @if($profesionalGenerador)
                            <div class="font-medium text-base text-gray-500 mb-2">
                                Profesional Creador: {{ $profesionalGenerador }}
                            </div>
                        @endif

                        <h3 class="font-medium text-base text-gray-700 mb-2">Otros profesionales participantes</h3>
                        <div class="selectors-row">
                            {{-- Selector de profesional--}}
                            <div class="selector-box" style="width: 35%;">
                                <select x-model="profesionalSeleccionado" @change="agregarProfesional()">
                                    <option value="">Agregar profesional</option>
                                    @foreach($profesionales as $prof)
                                        <option value="{{ $prof->id_profesional }}">
                                            {{ $prof->persona->nombre }} {{ $prof->persona->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- TABLA DINÁMICA DE PROFESIONALES SELECCIONADOS (Reemplaza a x-tabla-dinamica) --}}
                        <div class="mt-6">    
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>NOMBRE</th>
                                        <th>APELLIDO</th>
                                        <th>PROFESION</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="prof in profesionalesSeleccionados" :key="prof.id">
                                        <tr>
                                            <td x-text="prof.nombre"></td>
                                            <td x-text="prof.apellido"></td>
                                            <td x-text="prof.profesion"></td>
                                            <td>
                                                <button type="button" @click="eliminarProfesional(prof.id)" type="button" class="text-gray-400 hover:text-red-600 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </td>

                                            {{-- input hidden para enviar al backend---}}
                                            <input type="hidden" name="profesionales[]" :value="prof.id">
                                        </tr>
                                    </template>

                                    <tr x-show="profesionalesSeleccionados.length === 0">
                                        <td colspan="5" class="text-center">No hay profesionales seleccionados.</td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- TABLA EDITABLE DE OTROS ASISTENTES EXTERNOS --}}
                            <x-tabla-editable :listado="$otrosAsistentes" titulo="Otros asistentes externos" /> 
                        </div>
                        
                        
                        {{-- Inputs Ocultos para profesionales --}}
                        <template x-for="p in profesionalesSeleccionados" :key="p.id">
                            <input type="hidden" name="profesionales[]" :value="p.id">
                        </template>

                    </div>
                </div>

                {{-- MODALIDAD --}}
                <div class="space-y-6 mb-6">
                    <p class="separador">Modalidad</p>

                    @php
                        $tipoItems = array_map(fn($t) => $t->value, \App\Enums\Modalidad::cases());
                        $seleccionTipo = old('modalidad', $esEdicion ? ($intervencion->modalidad->value ?? '') : '');
                    @endphp

                    @if($esEdicion)
                        <p class="font-semibold text-gray-700">{{ $seleccionTipo }}</p>
                        <input type="hidden" name="modalidad" value="{{ $seleccionTipo }}">
                    @else
                        <div id="modalidad" x-data="{ modalidadSeleccionada: '{{ old('modalidad', $seleccionTipo) }}' }" x-show="true">
                            <div class="flex items-center space-x-4">
                                <x-opcion-unica
                                    :items="$tipoItems"
                                    name="modalidad"
                                    layout="horizontal"
                                    :seleccionTipo="$seleccionTipo"
                                    x-model="modalidadSeleccionada"
                                />

                                <template x-if="modalidadSeleccionada === 'OTRA'">
                                    <input
                                        name="otra_modalidad"
                                        value="{{ old('otra_modalidad') }}"
                                        placeholder="Especificar"
                                        class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 w-1/3"
                                    >
                                </template>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- DESCRIPCIÓN --}}
                <div class="space-y-8 mb-6">
                    <p class="separador">Descripción</p>

                    <div class="block text-sm font-medium text-gray-700">
                        <x-campo-requerido text="Objetivos" required />
                        <textarea name="temas_tratados" rows="3"
                                  class="w-full p-2 border border-gray-300 rounded-md">{{ old('temas_tratados', $esEdicion ? ($intervencion->temas_tratados ?? '') : '') }}</textarea>
                    </div>

                    <div class="block text-sm font-medium text-gray-700">
                        <x-campo-requerido text="Compromisos asumidos" required />
                        <textarea name="compromisos" rows="3"
                                  class="w-full p-2 border border-gray-300 rounded-md">{{ old('compromisos', $esEdicion ? ($intervencion->compromisos ?? '') : '') }}</textarea>
                    </div>

                    <div class="block text-sm font-medium text-gray-700">
                        <label>Observaciones</label>
                        <textarea name="observaciones" rows="3"
                                  class="w-full p-2 border border-gray-300 rounded-md">{{ old('observaciones', $esEdicion ? ($intervencion->observaciones ?? '') : '') }}</textarea>
                    </div>
                </div>

                {{-- Documentos (placeholder) --}}
                <div class="space-y-6 mb-6">
                    <p class="separador">Documentos</p>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-subir">Examinar</button>
                        <span class="text-sm text-gray-500">Formato: pdf, jpeg, png, doc. Máx 100Kb (placeholder)</span>
                    </div>

                    {{-- TODO: activar cuando exista la lógica de documentos --}}
                    @if(false)
                    @if($esEdicion && isset($intervencion->documentos) && $intervencion->documentos->isNotEmpty())
                        <div class="space-y-2 mt-2">
                            @foreach($intervencion->documentos as $doc)
                                <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                                    <span class="text-sm text-gray-600">{{ $doc->nombre ?? 'Documento' }}</span>
                                    <a href="{{ route('intervenciones.descargarDocumento', $doc->id ?? $doc->id_documento) }}" class="text-indigo-600">Descargar</a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @endif
                </div>

            </fieldset>

            {{-- BOTONES --}}
            <div class="fila-botones mt-8">
                @if(!$cerrado)
                    <button type="submit" class="btn-aceptar">{{ $esEdicion ? 'Actualizar' : 'Crear' }}</button>
                @endif

                @if($esEdicion)
                    <a class="btn-aceptar" href="{{ url()->previous() }}">Volver</a>
                @else
                    <a class="btn-aceptar" href="{{ route('intervenciones.principal') }}">Cancelar</a>
                @endif
            </div>
        </form>

            @if($esEdicion)
                <form action="{{ route('intervenciones.eliminar', $intervencion->id_intervencion) }}" method="POST" class="inline-block mt-2" onsubmit="return confirm('¿Seguro que querés eliminar esta intervención?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-eliminar">Eliminar Intervención</button>
                </form>
            @endif
    </div>
</div>

<script>
    function datosPersonas({ alumnoData = {}, alumnosIniciales = [], profesionalesData = {}, profesionalesIniciales = [] } = {}) {
        return {
            // Alumnos
            alumnoData: alumnoData || {},
            alumnosSeleccionados: Array.isArray(alumnosIniciales) ? alumnosIniciales : [],
            alumnoSeleccionado: "",
            aulaSeleccionada: "",

            // Profesionales
            profesionalSeleccionado: "",
            profesionalesData: profesionalesData || {},
            profesionalesSeleccionados: Array.isArray(profesionalesIniciales) ? profesionalesIniciales : [],

            agregarAlumno() {
                if (!this.alumnoSeleccionado || this.alumnoSeleccionado === "") return;

                let id = parseInt(this.alumnoSeleccionado);
                if (!alumnoData[id]) return;

                // Evitar duplicados
                if (!this.alumnosSeleccionados.find(a => a.id === id)) {
                    this.alumnosSeleccionados.push(alumnoData[id]);
                }
                this.alumnoSeleccionado = ""; // reseteo
            },

            agregarAula() {
                if (!this.aulaSeleccionada) return;

                // Buscar alumnos por aula
                let aulaId = parseInt(this.aulaSeleccionada);

                let alumnos = Object.values(alumnoData)
                    .filter(a => a.aula_id == aulaId);

                alumnos.forEach(a => {
                    if (!this.alumnosSeleccionados.find(x => x.id === a.id)) {
                        this.alumnosSeleccionados.push(a);
                    }
                });
            },

            eliminarAlumno(id) {
                this.alumnosSeleccionados = this.alumnosSeleccionados.filter(a => a.id !== id);
            },

            // Profesionales
            agregarProfesional() {
                if (!this.profesionalSeleccionado || this.profesionalSeleccionado === "") return;

                let id = parseInt(this.profesionalSeleccionado);
                if (!this.profesionalesData[id]) return;

                // Evitar duplicados
                if (!this.profesionalesSeleccionados.find(p => p.id === id)) {
                    this.profesionalesSeleccionados.push(this.profesionalesData[id]);
                }
                this.profesionalSeleccionado = ""; // reseteo
            },
            eliminarProfesional(id) {
                this.profesionalesSeleccionados = this.profesionalesSeleccionados.filter(p => p.id !== id);
            }
        };
    }
</script>


@endsection

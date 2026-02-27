@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Intervención' : 'Crear Intervención')

@section('contenido')

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

                {{-- Documentos --}}
                <div class="space-y-6 mb-6"
                    @if($esEdicion)
                    x-data="documentosIntervencion(
                        {{ json_encode($documentos ?? []) }},
                        '{{ route('intervenciones.subirDocumento', $intervencion->id_intervencion) }}'
                    )"
                    @confirm-accepted.window="onConfirmarEliminar()"
                    @endif>
                    <p class="separador">Documentos</p>

                    @if($esEdicion)
                    {{-- Upload --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <button type="button" class="btn-subir" @click="$refs.archivoInput.click()">Examinar</button>
                            <span class="text-sm text-gray-500">Solo PDF, JPEG, PNG, DOC, DOCX, XLS o XLSX · máx 10 MB</span>
                            <input type="file" x-ref="archivoInput" class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                @change="onArchivoChange($event)">
                        </div>

                        <div x-show="archivoSeleccionado" x-cloak class="flex items-center gap-3">
                            <input type="text" x-model="nombreDocumento"
                                class="flex-1 p-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Nombre del documento">
                            <span class="text-sm text-gray-500" x-text="archivoNombre"></span>
                            <button type="button" class="btn-aceptar text-sm px-3 py-1"
                                :disabled="cargando" @click="subirDocumento()">
                                <span x-show="!cargando">Subir</span>
                                <span x-show="cargando">Subiendo…</span>
                            </button>
                            <button type="button" class="btn-volver text-sm px-3 py-1" @click="cancelar()">Cancelar</button>
                        </div>

                        <p x-show="errorMsg" x-cloak class="text-red-600 text-sm" x-text="errorMsg"></p>
                    </div>

                    {{-- Lista activa --}}
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-700">Cargados:</p>
                        <template x-if="documentos.length === 0">
                            <p class="text-sm text-gray-400 italic">Sin documentos.</p>
                        </template>
                        <template x-for="doc in documentos" :key="doc.id_documento">
                            <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-xs font-semibold uppercase text-gray-500 w-12 shrink-0" x-text="doc.tipo_formato"></span>
                                    <a :href="doc.ruta_descarga" class="text-sm text-indigo-700 hover:underline truncate" x-text="doc.nombre"></a>
                                    <span class="text-xs text-gray-400 shrink-0" x-text="doc.tamanio + ' · ' + doc.fecha"></span>
                                </div>
                                <button type="button" class="text-gray-400 hover:text-red-500 ml-4 shrink-0"
                                    @click="pedirConfirmacionEliminar(doc)" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Pendientes de eliminación --}}
                    <div x-show="docsEliminados.length > 0" x-cloak class="space-y-2">
                        <p class="text-sm font-medium text-amber-600">Se eliminarán al actualizar:</p>
                        <template x-for="doc in docsEliminados" :key="doc.id_documento">
                            <div class="flex items-center justify-between p-2 bg-amber-50 border border-amber-200 rounded-md opacity-60">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-xs font-semibold uppercase text-gray-400 w-12 shrink-0" x-text="doc.tipo_formato"></span>
                                    <span class="text-sm text-gray-400 line-through truncate" x-text="doc.nombre"></span>
                                </div>
                                <button type="button" class="text-amber-600 hover:text-amber-800 ml-4 shrink-0 text-xs underline"
                                    @click="deshacerEliminar(doc)">Deshacer</button>
                            </div>
                        </template>
                    </div>

                    {{-- Hidden inputs para eliminación diferida --}}
                    <template x-for="id in idsAEliminar" :key="id">
                        <input type="hidden" name="docs_a_eliminar[]" :value="id">
                    </template>

                    @else
                    <p class="text-sm text-gray-400 italic">Los documentos se podrán cargar una vez guardada la intervención.</p>
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

    function documentosIntervencion(documentosIniciales, urlSubir) {
        return {
            documentos: documentosIniciales,
            archivoSeleccionado: false,
            archivoNombre: '',
            archivoArchivo: null,
            nombreDocumento: '',
            cargando: false,
            errorMsg: '',
            docPendienteEliminar: null,
            docsEliminados: [],
            idsAEliminar: [],

            onArchivoChange(event) {
                const file = event.target.files[0];
                if (!file) return;
                if (file.size > 10 * 1024 * 1024) {
                    this.errorMsg = 'El archivo supera el límite de 10 MB.';
                    event.target.value = '';
                    return;
                }
                const ext = file.name.split('.').pop().toLowerCase();
                const permitidos = ['pdf','jpg','jpeg','png','doc','docx','xls','xlsx'];
                if (!permitidos.includes(ext)) {
                    this.errorMsg = 'Formato no permitido. Use PDF, JPG, PNG, DOC, DOCX, XLS o XLSX.';
                    event.target.value = '';
                    return;
                }
                this.errorMsg = '';
                this.archivoArchivo = file;
                this.archivoNombre = file.name;
                this.nombreDocumento = file.name.replace(/\.[^.]+$/, '');
                this.archivoSeleccionado = true;
            },

            cancelar() {
                this.archivoSeleccionado = false;
                this.archivoNombre = '';
                this.archivoArchivo = null;
                this.nombreDocumento = '';
                this.errorMsg = '';
                this.$refs.archivoInput.value = '';
            },

            async subirDocumento() {
                if (!this.archivoArchivo || !this.nombreDocumento.trim()) {
                    this.errorMsg = 'Ingrese un nombre para el documento.';
                    return;
                }
                this.cargando = true;
                this.errorMsg = '';
                const formData = new FormData();
                formData.append('nombre', this.nombreDocumento.trim());
                formData.append('archivo', this.archivoArchivo);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                try {
                    const resp = await fetch(urlSubir, { method: 'POST', body: formData });
                    const json = await resp.json();
                    if (!resp.ok) {
                        this.$dispatch('abrir-modal-error', { message: json.error ?? 'Error al subir el documento.' });
                        return;
                    }
                    this.documentos.unshift(json);
                    this.cancelar();
                } catch (e) {
                    this.$dispatch('abrir-modal-error', { message: 'Error de red. Inténtelo de nuevo.' });
                } finally {
                    this.cargando = false;
                }
            },

            pedirConfirmacionEliminar(doc) {
                this.docPendienteEliminar = doc;
                this.$dispatch('abrir-modal-confirmar', {
                    message: `¿Eliminar el documento "${doc.nombre}"?`,
                    formId: null,
                });
            },

            onConfirmarEliminar() {
                if (!this.docPendienteEliminar) return;
                const doc = this.docPendienteEliminar;
                this.docPendienteEliminar = null;
                this.documentos = this.documentos.filter(d => d.id_documento !== doc.id_documento);
                this.docsEliminados.push(doc);
                this.idsAEliminar.push(doc.id_documento);
            },

            deshacerEliminar(doc) {
                this.docsEliminados = this.docsEliminados.filter(d => d.id_documento !== doc.id_documento);
                this.idsAEliminar = this.idsAEliminar.filter(id => id !== doc.id_documento);
                this.documentos.unshift(doc);
            },
        };
    }
</script>


@endsection

@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Plan de Acción' : 'Crear Plan de Acción')

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
    $esEdicion = isset($modo) && $modo === 'editar' && isset($planDeAccion);
    $cerrado = $esEdicion ? ($planDeAccion->activo === false) : false;

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
                    'fecha_nacimiento' => $persona->fecha_nacimiento
                        ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y')
                        : 'N/A',
                    'nacionalidad' => $persona->nacionalidad ?? 'N/A',
                    'domicilio' => $persona->domicilio ?? 'N/A',
                    'edad' => $persona->fecha_nacimiento
                        ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->age
                        : 'N/A',
                    'curso'   => $al->aula?->descripcion,
                    'aula_id' => $al->fk_id_aula,
                ]
            ];
        });

        $initialAlumnoId = $oldOr('alumno_seleccionado');
        $initialAlumnoInfo = $initialAlumnoId && $alumnosJson->has($initialAlumnoId)
                            ? $alumnosJson[$initialAlumnoId]
                            : null;

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

    //Profesional generador para mostrar en la vista
    $profesionalGenerador = null;
    if ($esEdicion && isset($planDeAccion->profesionalGenerador) && $planDeAccion->profesionalGenerador?->persona) {
        $pg = $planDeAccion->profesionalGenerador;
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
                <p>Este plan de acción está cerrado. No se permiten modificaciones.</p>
            @endif
        </div>

        <div class="flex justify-end space-x-4">
            @if($esEdicion)
                <x-boton-estado 
                    :activo="$planDeAccion->activo" 
                    :route="route('planDeAccion.cambiarActivo', $planDeAccion->id_plan_de_accion)"
                    :text_activo="'Cerrar'"
                    :text_inactivo="'Abrir'"
                />
            @endif
            <a class="btn-volver" href="{{ url()->previous() }}">Volver</a>
        </div>
    </div>

    {{-- Alpine para controlar secciones por tipo --}}
    <div x-data="{
        tipoPlanSeleccionado: '{{ old('tipo_plan', $esEdicion ? ($planDeAccion->tipo_plan->value ?? '') : '') }}',
        alumnosData: {{ $alumnosJson->toJson() }},
        alumnoSeleccionadoInfo: @json($initialAlumnoInfo),
        // nuevo: guardamos el id seleccionado (se envía como alumnos[] al backend)
        alumnoSeleccionadoId: '{{ $initialAlumnoId }}',
        
        seleccionarAlumno(id) {
            // aseguramos consistencia: actualizar info y el id seleccionado
            id = String(id || '');
            this.alumnoSeleccionadoId = id;
            this.alumnoSeleccionadoInfo = this.alumnosData[id] || null;
        }
    }">

        <form method="POST" action="{{ $esEdicion 
                ? route('planDeAccion.actualizar', $planDeAccion->id_plan_de_accion)
                : route('planDeAccion.store') 
            }}">
            @csrf

            {{-- Cuando se edita: método PUT --}}
            @if($esEdicion)
                @method('PUT')
            @endif

            {{-- Asegurar que el generador queda presente (fallback al usuario autenticado) --}}
            <input type="hidden" name="fk_id_profesional_generador" value="{{ old('fk_id_profesional_generador', auth()->user()->id_profesional ?? auth()->id()) }}">

            <fieldset {{ $cerrado ? 'disabled' : '' }}>
                {{-- TIPO --}}
                <div class="space-y-6 mb-6">
                    <p class="separador">Tipo de Plan</p>

                    @php
                        $tipoItems = array_map(fn($t) => $t->value, \App\Enums\TipoPlan::cases());
                        $seleccion = old('tipo_plan', $esEdicion ? ($planDeAccion->tipo_plan->value ?? '') : '');
                    @endphp

                    @if($esEdicion)
                        <p class="font-semibold text-gray-700">
                            {{ $seleccion }}
                        </p>
                        {{-- En edición incluimos el tipo en un input hidden para que el back valide correctamente --}}
                        <input type="hidden" name="tipo_plan" value="{{ $seleccion }}">
                    @else
                        <x-opcion-unica
                            :items="$tipoItems"
                            name="tipo_plan"
                            layout="horizontal"
                            :seleccion="$seleccion"
                            x-model="tipoPlanSeleccionado"
                        />
                    @endif
                </div>

                {{-- DESTINATARIO - Individual --}}
                <div id="destinatario-individual" x-data="planIndividual({ alumnosData: @js($alumnosJson), alumnosIniciales: @js($alumnosSeleccionados ?? []), initialAlumnoId: '{{ $initialAlumnoId ?? '' }}' })" x-init="if (initialAlumnoId) seleccionarAlumno(initialAlumnoId)" x-show="tipoPlanSeleccionado === 'INDIVIDUAL'" style="{{ ($esEdicion && ($planDeAccion->tipo_plan->value ?? '') === 'INDIVIDUAL') ? '' : 'display:none;' }}">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Destinatario</p>

                        {{-- Select simple para elegir UN alumno --}}
                        <div class="flex gap-4 mt-4">
                            <div class="flex flex-col w-1/3">
                                <label class="text-sm font-medium">Seleccionar alumno</label>
                                <!-- vinculado al id seleccionado y actualiza la info -->
                                <select name="alumno_seleccionado" class="border px-2 py-1 rounded" x-model="alumnoSeleccionadoId" x-on:change="seleccionarAlumno($event.target.value)">
                                    <option value="">-- Seleccionar alumno --</option>
                                    @foreach($alumnos as $al)
                                        @php
                                            $alId = $al->id_alumno ?? $al->id ?? null;
                                            $label = $al->persona->nombre . ' ' . $al->persona->apellido;
                                        @endphp
                                        <option value="{{ $alId }}"
                                            {{ $oldOr('alumno_seleccionado', $esEdicion ? ($planDeAccion->alumnos->first()->id_alumno ?? '') : '') == $alId ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                
                            </div>
                        </div>
                        {{-- FRAGMENTO DE INFORMACIÓN PERSONAL DEL ALUMNO --}}
                        <div x-show="alumnoSeleccionadoInfo" class="mt-8 p-4">
                            <h3 class="font-medium text-base text-gray-700 mb-4 border-b pb-2">Información Personal del Alumno</h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-y-4 gap-x-6 text-sm">
                                
                                {{-- Fila 1 --}}
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">Nombre y Apellido:</span>
                                    <p class="font-semibold text-gray-800" x-text="`${alumnoSeleccionadoInfo.apellido}, ${alumnoSeleccionadoInfo.nombre}`"></p>
                                </div>
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">DNI:</span>
                                    <p class="font-semibold text-gray-800" x-text="alumnoSeleccionadoInfo.dni"></p>
                                </div>
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">Fecha de nacimiento:</span>
                                    <p class="font-semibold text-gray-800" x-text="alumnoSeleccionadoInfo.fecha_nacimiento"></p>
                                </div>
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">Edad:</span>
                                    <p class="font-semibold text-gray-800" x-text="alumnoSeleccionadoInfo.edad"></p>
                                </div>

                                {{-- Fila 2 --}}
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">Nacionalidad:</span>
                                    <p class="font-semibold text-gray-800" x-text="alumnoSeleccionadoInfo.nacionalidad"></p>
                                </div>
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">Domicilio:</span>
                                    <p class="font-semibold text-gray-800" x-text="alumnoSeleccionadoInfo.domicilio"></p>
                                </div>
                                <div class="col-span-1">
                                    <span class="font-medium text-gray-600">Curso:</span>
                                    <p class="font-semibold text-gray-800" x-text="alumnoSeleccionadoInfo.curso"></p>
                                </div>
                            </div>
                        </div>
                        <!-- input oculto que enviará siempre alumnos[] cuando el tipo sea INDIVIDUAL -->
                                <template x-if="alumnoSeleccionadoId">
                                    <input type="hidden" name="alumnos[]" :value="alumnoSeleccionadoId">
                                </template>
                    </div>
                </div>

                {{-- DESTINATARIO - Grupal --}}
                <div id="destinatario-grupal" 
                x-data="planGrupal({ alumnosData: @js($alumnosJson), alumnosIniciales: @js($alumnosSeleccionados ?? []) })" x-show="tipoPlanSeleccionado === 'GRUPAL'" 
                style="{{ ($esEdicion && ($planDeAccion->tipo_plan->value ?? '') === 'GRUPAL') ? '' : 'display:none;' }}">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Destinatarios</p>
                        <div class="selectors-row">
                            {{-- Selector de alumno--}}
                            <div class="selector-box" style="width: 35%;">
                                <select x-model="alumnoSeleccionado" @change="agregarAlumno()">
                                    <option value="">Agregar alumno</option>
                                    @foreach($alumnos as $al)
                                        <option value="{{ $al->id_alumno }}">
                                            {{ $al->persona->nombre }} {{ $al->persona->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Selección de aula --}}
                            <div class="selector-box" style="width: 20%;">
                                <select x-model="aulaSeleccionada" @change="agregarAula()">
                                    <option value="">Agregar aula</option>
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

                                            {{-- input hidden para enviar al backend---}}
                                            <input type="hidden" name="alumnos[]" :value="al.id">
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

                {{-- CAMPOS COMUNES: Objetivos / Acciones / Observaciones --}}
                <div class="space-y-8 mb-6">
                    <p class="separador">Descripción</p>

                    <div class="block text-sm font-medium text-gray-700">
                        <x-campo-requerido text="Objetivos" required />
                        <textarea name="objetivos" rows="3"
                                  class="w-full p-2 border border-gray-300 rounded-md">{{ old('objetivos', $esEdicion ? ($planDeAccion->objetivos ?? '') : '') }}</textarea>
                    </div>

                    <div class="block text-sm font-medium text-gray-700">
                        <x-campo-requerido text="Acciones a realizar" required />
                        <textarea name="acciones" rows="3"
                                  class="w-full p-2 border border-gray-300 rounded-md">{{ old('acciones', $esEdicion ? ($planDeAccion->acciones ?? '') : '') }}</textarea>
                    </div>

                    <div class="block text-sm font-medium text-gray-700">
                        <label>Observaciones</label>
                        <textarea name="observaciones" rows="3"
                                  class="w-full p-2 border border-gray-300 rounded-md">{{ old('observaciones', $esEdicion ? ($planDeAccion->observaciones ?? '') : '') }}</textarea>
                    </div>
                </div>

                {{-- Si el plan es INSTITUCIONAL en edición, mostrar el profesional creador en la sección de Profesionales --}}
                @if($esEdicion && (($planDeAccion->tipo_plan->value ?? '') === 'INSTITUCIONAL'))
                    <div class="space-y-6 mb-6">
                        <p class="separador">Profesionales</p>
                        @if($profesionalGenerador)
                            <div class="font-medium text-base text-gray-700 mb-2">
                                <strong>Profesional creador: {{ $profesionalGenerador }}</strong>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- RESPONSABLES  --}}
                <div id="responsables2" 
                x-data="planGrupal({ profesionalesData: @js($profesionalesJson), profesionalesIniciales: @js($profesionalesSeleccionados ?? []) })" x-show="tipoPlanSeleccionado === 'INDIVIDUAL' || tipoPlanSeleccionado === 'GRUPAL'"
                style="{{ ($esEdicion && in_array(($planDeAccion->tipo_plan->value ?? ''), ['INDIVIDUAL','GRUPAL'])) ? '' : 'display:none;' }}">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Profesionales participantes</p>

                        {{-- Mostrar profesional generador--}}
                        @if($profesionalGenerador)
                            <div class="font-medium text-base text-gray-700 mb-2">
                                <strong>Profesional Creador: {{ $profesionalGenerador }}</strong>
                            </div>
                        @endif
                        
                        <div class="selectors-row">
                            {{-- Selector de profesional--}}
                            <div class="selector-box" style="width: 35%;">
                                <select x-model="profesionalSeleccionado" @change="agregarProfesional()">
                                    <option value="">Agregar otros profesionales</option>
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
                            
                        </div>
                        
                        {{-- Inputs Ocultos para profesionales --}}
                        <template x-for="p in profesionalesSeleccionados" :key="p.id">
                            <input type="hidden" name="profesionales[]" :value="p.id">
                        </template>

                    </div>
                </div>

                {{-- INTERVENCIONES RELACIONADAS --}}
                <div id="intervenciones-asociadas" x-data="{ intervenciones: @js($intervencionesAsociadas ?? []) }">                    
                    <div class="space-y-6 mb-6">
                        <p class="separador">Intervenciones Asociadas</p>
                        <x-tabla-dinamica 
                            :columnas="[
                                ['key' => 'fecha_hora_intervencion', 'label' => 'Fecha y Hora'],
                                ['key' => 'tipo_intervencion', 'label' => 'Tipo'],
                                ['key' => 'estado', 'label' => 'Estado'],
                            ]"
                            :filas="$intervencionesAsociadas"
                            idCampo="id_intervencion"
                            :filaEnlace="fn($fila) => route('intervenciones.editar', data_get($fila, 'id_intervencion'))"
                        >
                        </x-tabla-dinamica>
                    </div>
                </div>


                {{-- Documentos --}}
                <div class="space-y-6 mb-6"
                    @if($esEdicion)
                    x-data="documentosPlanDeAccion(
                        {{ json_encode($documentos ?? []) }},
                        '{{ route('planDeAccion.subirDocumento', $planDeAccion->id_plan_de_accion) }}'
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
                    <a class="btn-aceptar" href="{{ route('planDeAccion.principal') }}">Cancelar</a>
                @endif
            </div>
        </form>

            @if($esEdicion)
                <form action="{{ route('planDeAccion.eliminar', $planDeAccion->id_plan_de_accion) }}" method="POST" class="inline-block mt-2" onsubmit="return confirm('¿Seguro que querés eliminar este plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-eliminar">Eliminar Plan</button>
                </form>
            @endif
    </div>
</div>

<script>
    function planGrupal({ alumnosData = {}, alumnosIniciales = [], profesionalesData = {}, profesionalesIniciales = [] } = {}) {
        return {
            // Alumnos
            alumnoSeleccionado: "",
            aulaSeleccionada: "",
            alumnosSeleccionados: Array.isArray(alumnosIniciales) ? alumnosIniciales : [],

            // Profesionales
            profesionalSeleccionado: "",
            profesionalesData: profesionalesData || {},
            profesionalesSeleccionados: Array.isArray(profesionalesIniciales) ? profesionalesIniciales : [],

            agregarAlumno() {
                if (!this.alumnoSeleccionado || this.alumnoSeleccionado === "") return;

                let id = parseInt(this.alumnoSeleccionado);
                if (!alumnosData[id]) return;

                // Evitar duplicados
                if (!this.alumnosSeleccionados.find(a => a.id === id)) {
                    this.alumnosSeleccionados.push(alumnosData[id]);
                }
                this.alumnoSeleccionado = ""; // reseteo
            },

            agregarAula() {
                if (!this.aulaSeleccionada) return;

                // Buscar alumnos por aula
                let aulaId = parseInt(this.aulaSeleccionada);

                let alumnos = Object.values(alumnosData)
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
    function planIndividual({ alumnosData, alumnosIniciales, initialAlumnoId = null }) {
        return {
            alumnosData,
            alumnoSeleccionadoId: initialAlumnoId,
            alumnoSeleccionadoInfo: initialAlumnoId ? alumnosData[initialAlumnoId] : null,

            seleccionarAlumno(id) {
                this.alumnoSeleccionadoId = id;
                this.alumnoSeleccionadoInfo = this.alumnosData[id] ?? null;
            }
        }
    }

    function documentosPlanDeAccion(documentosIniciales, urlSubir) {
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

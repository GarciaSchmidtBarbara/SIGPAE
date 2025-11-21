@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Plan de Acción' : 'Crear Plan de Acción')

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

@php
    $esEdicion = isset($modo) && $modo === 'editar' && isset($planDeAccion);
    $cerrado = $esEdicion ? ($planDeAccion->activo === false) : false;

    // helper para valores viejos del modelo
    $oldOr = fn($field, $fallback = null) => old($field, $fallback);

    // Preparar datos de alumnos para Alpine.js
    $alumnosJson = collect($alumnos)->mapWithKeys(function ($al) {
        $persona = $al->persona;
        return [
            $al->id_alumno => [
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'dni' => $persona->dni,
                'fecha_nacimiento' => $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : 'N/A',
                'nacionalidad' => $persona->nacionalidad ?? 'N/A',
                'domicilio' => $persona->domicilio ?? 'N/A',
                'edad' => $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->age : 'N/A',
                // Asumiendo que 'curso' está en el modelo Alumno CAMBIAR ESTO
                'curso' => $al->curso ?? 'N/A', 
            ]
        ];
    });

    $initialAlumnoId = $oldOr('alumno_seleccionado', $esEdicion ? ($planDeAccion->alumnos->first()->id_alumno ?? '') : '');
    $initialAlumnoInfo = $initialAlumnoId && $alumnosJson->has($initialAlumnoId) ? $alumnosJson[$initialAlumnoId] : null;

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
            <a class="btn-volver" href="{{ route('planDeAccion.principal') }}">Volver</a>
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
                <div id="destinatario-individual" x-show="tipoPlanSeleccionado === 'INDIVIDUAL'" style="display:none;">
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

                                <!-- input oculto que enviará siempre alumnos[] cuando el tipo sea INDIVIDUAL -->
                                <input type="hidden" name="alumnos[]" :value="alumnoSeleccionadoId" x-show="alumnoSeleccionadoId">
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
                    </div>
                </div>

                {{-- DESTINATARIO - Grupal --}}
                <div id="destinatario-grupal" 
                x-data="destinatarioGrupal($root.alumnosData, {{ json_encode($alumnosSeleccionados ?? []) }})" x-show="tipoPlanSeleccionado === 'GRUPAL'" 
                style="display:none;">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Destinatarios</p>

                        {{-- Selector de alumno--}}
                        <div class="flex gap-4 mt-4">
                            <div class="flex flex-col w-1/3">
                                <label class="text-sm font-medium">Seleccionar alumno</label>
                                <select class="border px-2 py-1 rounded"                                x-on:change="seleccionarAlumno($event.target.value); $event.target.value=''">
                                    <option value="">-- Seleccionar alumno --</option>
                                    @foreach($alumnos as $al)
                                        @php
                                            $alId = $al->id_alumno ?? $al->id ?? null;
                                            $label = $al->persona->nombre . ' ' . $al->persona->apellido;
                                        @endphp
                                        <option value="{{ $alId }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Selección de aula --}}
                        <div class="flex gap-4 mt-4">
                            <div class="flex flex-col w-1/4">
                                <label class="text-sm font-medium">Aula</label>
                                <select name="aula" class="border px-2 py-1 rounded">
                                    <option value="">-- Seleccionar aula --</option>
                                    @foreach($aulas as $aula)
                                        {{-- $aula puede tener id_aula o id; soportamos ambos --}}
                                        @php $aulaId = $aula->id_aula ?? $aula->id ?? null; @endphp
                                        <option value="{{ $aulaId }}"
                                            {{ $oldOr('aula', $esEdicion ? ($planDeAccion->aulas->first()->id_aula ?? '') : '') == $aulaId ? 'selected' : '' }}>
                                            {{ $aula->descripcion ?? ($aula->curso.'°'.$aula->division ?? $aulaId) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- TABLA DINÁMICA DE ALUMNOS SELECCIONADOS (Reemplaza a x-tabla-dinamica) --}}
                        <div class="mt-6">
                            <h3 class="font-medium text-base text-gray-700 mb-2">Alumnos Seleccionados</h3>
                            
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellido</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                                            <th class="px-4 py-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" x-cloak>
                                        {{-- Iteración dinámica sobre el array 'alumnos' de Alpine --}}
                                        <template x-for="alumno in alumnos" :key="alumno.id">
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="alumno.nombre"></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="alumno.apellido"></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="alumno.dni"></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="alumno.curso"></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                                    <button type="button" 
                                                            class="text-gray-400 hover:text-red-600 focus:outline-none"
                                                            x-on:click="eliminarAlumno(alumno.id)">
                                                        {{-- Icono de bote de basura --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="alumnos.length === 0">
                                            <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                                                No hay alumnos seleccionados.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        {{-- Inputs Ocultos (Mantener) --}}
                        <template x-for="alumno in alumnos" :key="alumno.id">
                            <!-- normalizamos el nombre a 'alumnos[]' para que el backend reciba un único campo consistente -->
                            <input type="hidden" name="alumnos[]" :value="alumno.id">
                        </template>
                    </div>
                </div>
                <script>
                function destinatarioGrupal(allAlumnos, initialAlumnos) {
                    // Aseguramos que los datos iniciales se usen si existen, 
                    // y que cada elemento tenga un 'id' para que la función eliminarAlumno funcione.
                    const initial = Array.isArray(initialAlumnos) ? initialAlumnos.map(a => ({
                        id: a.id_alumno || a.id,
                        nombre: a.nombre, // Asumiendo que el controlador mapeó estos datos
                        apellido: a.apellido,
                        dni: a.dni,
                        curso: a.curso,
                    })) : [];

                    return {
                        // Inicializamos con los alumnos ya guardados o un array vacío
                        alumnos: initial, 

                        seleccionarAlumno(alumnoId) {
                            // Aseguramos que el ID sea un string para coincidir con las keys del JSON
                            alumnoId = String(alumnoId);
                            if (!alumnoId || alumnoId === '0' || !allAlumnos[alumnoId]) return;

                            // 1. Evitar duplicados
                            if (this.alumnos.find(a => String(a.id) === alumnoId)) return;

                            // 2. Obtener datos del alumno
                            const alumnoData = allAlumnos[alumnoId];
                            
                            // 3. Mapear al formato de la tabla
                            const nuevoAlumno = {
                                id: alumnoId, // Usamos el ID del select
                                nombre: alumnoData.nombre,
                                apellido: alumnoData.apellido,
                                dni: alumnoData.dni,
                                curso: alumnoData.curso
                            };
                            
                            this.alumnos.push(nuevoAlumno);
                        },

                        eliminarAlumno(id) {
                            // Filtra el array, manteniendo solo los alumnos cuyo id NO coincida con el id a eliminar
                            this.alumnos = this.alumnos.filter(a => String(a.id) !== String(id));
                        }
                    }
                }
                </script>

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

                {{-- RESPONSABLES (visible para Individual/Grupal) --}}
                <div id="responsables" x-show="tipoPlanSeleccionado === 'INDIVIDUAL' || tipoPlanSeleccionado === 'GRUPAL'" style="display:none;">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Responsables (Profesionales)</p>

                        <div class="fila-botones mb-4">
                            <button type="button" class="btn-aceptar" @click.prevent="/* buscar profesional */">Buscar profesional</button>
                            <button type="button" class="btn-aceptar" @click.prevent="/* agregar profesional */">Agregar profesional</button>
                        </div>

                        <div class="space-y-2">
                            {{-- Select múltiple para profesionales participantes --}}
                            <label class="block text-sm font-medium">Profesionales participantes</label>
                            <select name="profesionales[]" multiple class="border px-2 py-1 rounded w-full">
                                @foreach($profesionales as $prof)
                                    @php $profId = $prof->id_profesional ?? $prof->id ?? null; @endphp
                                    @php
                                        $selectedProfs = old('profesionales', isset($planDeAccion) ? $planDeAccion->profesionalesParticipantes->pluck('id_profesional')->toArray() : []);
                                    @endphp
                                    <option value="{{ $profId }}" {{ in_array($profId, (array)$selectedProfs) ? 'selected' : '' }}>
                                        {{ $prof->persona->apellido ?? ($prof->nombre ?? $profId) }} {{ $prof->persona->nombre ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                    @if($esEdicion && isset($planDeAccion->documentos) && $planDeAccion->documentos->isNotEmpty())
                        <div class="space-y-2 mt-2">
                            @foreach($planDeAccion->documentos as $doc)
                                <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                                    <span class="text-sm text-gray-600">{{ $doc->nombre ?? 'Documento' }}</span>
                                    <a href="{{ route('planDeAccion.descargarDocumento', $doc->id ?? $doc->id_documento) }}" class="text-indigo-600">Descargar</a>
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
                    <a class="btn-aceptar" href="{{ route('planDeAccion.principal') }}">Volver</a>
                @else
                    <a class="btn-aceptar" href="{{ route('planDeAccion.principal') }}">Cancelar</a>
                @endif
            </div>
        </form>

            @if($esEdicion)
                <form action="{{ route('planDeAccion.eliminar', $planDeAccion->id_plan_de_accion) }}" method="POST" class="inline-block mt-2" onsubmit="return confirm('¿Seguro que querés eliminar este plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                </form>
            @endif
    </div>
</div>


@endsection

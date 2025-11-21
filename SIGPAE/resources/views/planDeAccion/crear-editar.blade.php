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
            tipoPlanSeleccionado: '{{ old('tipo_plan', $esEdicion ? ($planDeAccion->tipo_plan->value ?? '') : '') }}'
        }">

        <form method="POST" action="{{ $esEdicion 
                ? route('planDeAccion.iniciar-edicion', $planDeAccion->id_plan_de_accion)
                : route('planDeAccion.store') 
            }}">
            @csrf
            @if($esEdicion)
                @method('PUT')
            @endif

            <fieldset {{ $cerrado ? 'disabled' : '' }}>
                {{-- TIPO --}}
                <div class="space-y-6 mb-6">
                    <p class="separador">Tipo de Plan</p>

                    {{-- Usamos tu componente x-opcion-unica y le pasamos x-model --}}
                    @php
                        // pasamos los valores tal cual vienen del enum
                        $tipoItems = array_map(fn($t) => $t->value, \App\Enums\TipoPlan::cases());
                    @endphp

                    <x-opcion-unica
                        :items="$tipoItems"
                        name="tipo_plan"
                        layout="horizontal"
                        :seleccion="old('tipo_plan', $esEdicion ? ($planDeAccion->tipo_plan->value ?? '') : '')"
                        x-model="tipoPlanSeleccionado"
                    />
                </div>

                {{-- DESTINATARIO - Individual --}}
                <div id="destinatario-individual" x-show="tipoPlanSeleccionado === 'INDIVIDUAL'" style="display:none;">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Destinatario</p>

                        <div class="fila-botones">
                            {{-- Botones para buscar/seleccionar alumno; aquí dejamos enlaces o triggers --}}
                            <button type="button" class="btn-aceptar" @click.prevent="/* abrir modal buscar alumno */">Buscar alumno</button>
                            <button type="button" class="btn-aceptar" @click.prevent="/* crear nuevo alumno */">Crear alumno</button>
                        </div>

                        {{-- Select simple para elegir UN alumno --}}
                        <div class="flex gap-4 mt-4">
                            <div class="flex flex-col w-1/3">
                                <label class="text-sm font-medium">Seleccionar alumno</label>
                                <select name="alumno_seleccionado" class="border px-2 py-1 rounded">
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

                    </div>
                </div>

                {{-- DESTINATARIO - Grupal --}}
                <div id="destinatario-grupal" x-show="tipoPlanSeleccionado === 'GRUPAL'" style="display:none;">
                    <div class="space-y-6 mb-6">
                        <p class="separador">Destinatarios</p>

                        <div class="fila-botones">
                            <button type="button" class="btn-aceptar" @click.prevent="/* buscar alumnos para agregar */">Buscar alumnos</button>
                            <button type="button" class="btn-aceptar" @click.prevent="/* buscar aula */">Seleccionar aula</button>
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

                            {{-- Opcional: mostrar alumnos asignados al grupo (lista breve) --}}
                            <div class="flex-1">
                                <label class="text-sm font-medium">Alumnos incluidos</label>
                                <div class="mt-2">
                                    @if(isset($planDeAccion) && $esEdicion && $planDeAccion->alumnos->isNotEmpty())
                                        <ul class="list-disc pl-5 text-sm">
                                            @foreach($planDeAccion->alumnos as $a)
                                                <li>{{ $a->persona->nombre }} {{ $a->persona->apellido }} ({{ $a->persona->dni ?? '' }})</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-500">No hay alumnos agregados.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

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

                @if($esEdicion)
                    <form action="{{ route('planDeAccion.eliminar', $planDeAccion->id_plan_de_accion) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('¿Seguro que querés eliminar este plan?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</div>

@endsection

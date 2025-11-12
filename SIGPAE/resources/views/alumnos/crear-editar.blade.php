@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Alumno' : 'Crear Alumno')

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
    $esEdicion = isset($modo) && $modo === 'editar' && isset($alumno);
    $inactivo = $esEdicion ? ($alumno->persona->activo === false) : false;
@endphp

@if($esEdicion)
    <div class="mt-4 my-4 flex justify-between items-center">
        <div class="text-sm text-red-600 min-h-[1.5rem]">
            @if($inactivo)
                <p class="text-red-600 text-sm">Este alumno está inactivo. No se permiten modificaciones.</p>
            @endif
        </div>

        <div class="flex space-x-4">
            <x-boton-estado 
                :activo="$alumno->persona->activo" 
                :route="route('alumnos.cambiarActivo', $alumno->id_alumno)" 
            />
            <a class="btn-volver" href="{{ route('alumnos.principal') }}">Volver</a>
        </div>
    </div>
@endif

<div x-data="{
    familyMembers: {{ json_encode(array_values($familiares_temp ?? [])) }},
    
    async removeFamiliar(index) {
        if (confirm('¿Estás seguro de eliminar este familiar?')) {
            try {
                const response = await fetch(`/familiares/temp/${index}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.familyMembers.splice(index, 1);
                } else {
                    alert('Error al eliminar el familiar');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el familiar');
            }
        }
    }
}">
    
    <form method="POST" action="{{ isset($modo) && $modo === 'editar' 
            ? route('alumnos.actualizar', $alumno->id_alumno)
            : route('alumnos.store') }}">
        @csrf
        @if($esEdicion)
            @method('PUT')
        @endif
        <fieldset {{ $inactivo ? 'disabled' : '' }}>
        <div class="space-y-8 mb-6">
            <p class="separador">Información Personal del Alumno</p>
            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Documento" required />
                    <input name="dni" value="{{ $alumnoData['dni'] ?? old('dni') }}" placeholder="Documento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nombres" required />
                    <input name="nombre" value="{{ $alumnoData['nombre'] ?? old('nombre') }}" placeholder="Nombres" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Apellidos" required />
                    <input name="apellido" value="{{ $alumnoData['apellido'] ?? old('apellido') }}" placeholder="Apellidos" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <x-campo-fecha-edad
                    label="Fecha de nacimiento"
                    name="fecha_nacimiento"
                    :value="old(
                        'fecha_nacimiento',
                        isset($alumno) && isset($alumno->persona->fecha_nacimiento)
                            ? \Illuminate\Support\Carbon::parse($alumno->persona->fecha_nacimiento)->format('Y-m-d')
                            : ''
                    )"
                    edad-name="edad"
                    :edad-value="old('edad', $alumnoData['edad'] ?? '')"
                    required
                />
            </div>

            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nacionalidad" required />
                    <input name="nacionalidad" value="{{ $alumnoData['nacionalidad'] ?? old('nacionalidad') }}" placeholder="Nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Aula" required />
                    <select name="aula" id="aula" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar aula</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso }}" @selected(($alumnoData['aula'] ?? old('aula')) == $curso)>{{ $curso }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Cantidad inasistencias</p>
                    <input name="inasistencias" value="{{ $alumnoData['inasistencias'] ?? old('inasistencias') }}" placeholder="Inasistencias" type="number" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="space-y-2">
                    <x-campo-requerido text="Tiene CUD" required />
                    <x-opcion-unica 
                        :items="['Sí', 'No']"
                        name="cud"
                        layout="horizontal"
                         :seleccion="$alumnoData['cud'] ?? old('cud', 'No')" 
                    />
                </div>
            </div>  
        </div>

        <div class="space-y-8 mb-6">
            <p class="separador">Red Familiar</p>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellido</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relacion</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefono</th>
                            <th class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Bucle para mostrar familiares temporales cargados --}}
                        <template x-for="(familiar, index) in familyMembers" :key="index">
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.nombre"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.apellido"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.dni"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.parentesco"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.telefono_personal"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                    <button @click.prevent="removeFamiliar(index)" type="button" class="text-gray-400 hover:text-red-600 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            {{--
                ACCIÓN CLAVE: Aquí se debe enviar la data del formulario actual al controller (GET o POST)
                para que el controller guarde la data del alumno en la sesión
                y luego redirija a route('familiares.create') FALTA IMPLEMENTAR LA  LOGICA DE GUARDADO EN SESSION
            --}}
                        <button type="submit" class="btn-aceptar" formaction="{{ route('alumnos.prepare-familiar') }}" formmethod="POST" formnovalidate>Crear Familiar</button>
        </div>

        <div class="space-y-8 mb-6">
            <p class="separador">Situación Integral</p>
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación socioeconómica </label>
            <textarea name="situacion_socioeconomica" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['situacion_socioeconomica'] ?? old('situacion_socioeconomica') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación familiar</label>
            <textarea name="situacion_familiar" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['situacion_familiar'] ?? old('situacion_familiar') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación medica </label>
            <textarea  name="situacion_medica" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['situacion_medica'] ?? old('situacion_medica') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación escolar </label>
            <textarea name="situacion_escolar" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['situacion_escolar'] ?? old('situacion_escolar') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Actividades extraescolares </label>
            <textarea name="actividades_extraescolares" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['actividades_extraescolares'] ?? old('actividades_extraescolares') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Intervenciones externas</label>
            <textarea name="intervenciones_externas" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['intervenciones_externas'] ?? old('intervenciones_externas') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Antecedentes</label>
            <textarea name="antecedentes" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['antecedentes'] ?? old('antecedentes') }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2">{{ $alumnoData['observaciones'] ?? old('observaciones') }}</textarea>      
        </div>
        </fieldset>
       
        <div class="space-y-8">
            <p class="separador">Documentación</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-subir">Examinar</button>
                        <span class="text-sm text-gray-500">Solo archivos en formato pdf, jpeg, png o doc con menos de 100Kb</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Cargados:</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                            <span class="text-sm text-gray-600">Documento1.pdf</span>
                            <button type="button" class="text-gray-500 hover:text-red-500">
                            </button>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                            <span class="text-sm text-gray-600">Documento2.pdf</span>
                            <button type="button" class="text-gray-500 hover:text-red-500">
                            </button>
                        </div>
                    </div>
                </div>
        </div>

        <div class="fila-botones mt-8">
            @if(!$inactivo)
                <button type="submit" class="btn-aceptar">Guardar</button>
            @endif   
        </div>
    </form>
   
    @if($esEdicion)
    <div class="mt-4 my-4 flex justify-between items-center">
        <div class="text-sm text-red-600 min-h-[1.5rem]">
            @if($inactivo)
                <p class="text-red-600 text-sm">Este alumno está inactivo. No se permiten modificaciones.</p>
            @endif
        </div>

        <div class="flex space-x-4">
            <x-boton-estado 
                :activo="$alumno->persona->activo" 
                :route="route('alumnos.cambiarActivo', $alumno->id_alumno)" 
            />
            <a class="btn-volver" href="{{ route('alumnos.principal') }}">Volver</a>
        </div>
    </div>
@endif
</div>
@endsection
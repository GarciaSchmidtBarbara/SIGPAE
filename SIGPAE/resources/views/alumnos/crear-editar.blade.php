@extends('layouts.base')

@section('encabezado', 'Crear Alumno')

@section('contenido')
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

    <form method="POST" action="{{ route('alumnos.store') }}">
        @csrf
        <div class="space-y-8 mb-6">
            <p class="separador">Información Personal del Alumno</p>
            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Documento</p>
                    <input name="dni" value="{{ $alumnoData['dni'] ?? old('dni') }}" placeholder="Documento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Nombres</p>
                    <input name="nombre" value="{{ $alumnoData['nombre'] ?? old('nombre') }}" placeholder="Nombres" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Apellidos</p>
                    <input name="apellido" value="{{ $alumnoData['apellido'] ?? old('apellido') }}" placeholder="Apellidos" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</p>
                    <input name="fecha_nacimiento" value="{{ $alumnoData['fecha_nacimiento'] ?? old('fecha_nacimiento') }}" type="date" placeholder="Fecha de nacimiento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Edad</p>
                    <input name="edad" value="{{ $alumnoData['edad'] ?? old('edad') }}" placeholder="Edad" type="number" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</p>
                    <input name="nacionalidad" value="{{ $alumnoData['nacionalidad'] ?? old('nacionalidad') }}" placeholder="Nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <label for="aula" class="text-sm font-medium text-gray-700 mb-1">Aula</label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tiene CUD</label>
                    <x-opcion-unica 
                        :items="['Sí', 'No']"
                        name="cud"
                        layout="horizontal"
                        :valor-seleccionado="$alumnoData['cud'] ?? old('cud', 'No')" 
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
                        <button type="submit" class="btn-aceptar" formaction="{{ route('alumnos.prepare-familiar') }}" formmethod="POST">Crear Familiar</button>
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

        <div class="space-y-8">
        </div>

       
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
            <button type="submit" class="btn-aceptar">Guardar</button>
            <button type="button" class="btn-eliminar" >Desactivar</button>
            <a class="btn-volver" href="{{ route('alumnos.principal') }}" >Volver</a>
        </div>
    </form>
</div>
@endsection
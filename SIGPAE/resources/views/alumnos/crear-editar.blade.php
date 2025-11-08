@extends('layouts.base')

@section('encabezado', 'Crear Alumno')

@section('contenido')

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
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



<form method="POST" action="{{ route('alumnos.store') }}">

    @csrf
    <div class="space-y-8 mb-6">
        <p class="separador">Información Personal del Alumno</p>
        <div class="fila-botones mt-8" x-show="true">
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Documento</p>
                <input name="dni" placeholder="Documento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Nombres</p>
                <input name="nombre" placeholder="Nombres" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Apellidos</p>
                <input name="apellido" placeholder="Apellidos" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</p>
                <input name="fecha_nacimiento" placeholder="Fecha de nacimiento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="fila-botones mt-8" x-show="true">
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Edad</p>
                <input name="edad" placeholder="Edad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</p>
                <input name="nacionalidad" placeholder="Nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex flex-col w-1/5">
                <label for="aula" class="text-sm font-medium text-gray-700 mb-1">Aula</label>
                <select name="aula" id="aula" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar aula</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso }}">{{ $curso }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col w-1/5">
                <p class="text-sm font-medium text-gray-700 mb-1">Cantidad inasistencias</p>
                <input name="inasistencias" placeholder="Inasistencias" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiene CUD</label>
                <x-opcion-unica 
                    :items="['Sí', 'No']"
                    name="cud"
                    layout="horizontal"
                    x-model="cudSeleccionado" 
                />
            </div>
        </div>  
    </div>

    <div class="space-y-8 mb-6">
        <p class="separador">Red Familiar</p>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nombre
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Apellido
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Documento
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Relacion
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Telefono
                    </th>
                    <th class="px-4 py-2 w-10">
                        {{-- Columna para el ícono de eliminar --}}
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- 
                       Aquí iría un bucle @foreach para listar los familiares ya cargados.
                       Por ahora, dejamos filas de ejemplo.
                    --}}
                    
                    {{-- Fila de ejemplo 1 --}}
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Juan </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Perez</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">12345678</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Padre</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">2901-5845695</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button class="text-gray-400 hover:text-red-600 focus:outline-none">
                                {{-- Icono de bote de basura (Tailwind Heroicons) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    
                    {{-- Fila de ejemplo 2 (Puedes duplicar la estructura para más filas) --}}
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Ivan </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Perez</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">12345678</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Abuelo</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">2901-5845578</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button class="text-gray-400 hover:text-red-600 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <a class="btn-aceptar" href="" >Crear Familiar</a>
    </div>

    <div class="space-y-8 mb-6">
        <p class="separador">Situación Integral</p>
        <label class="block text-sm font-medium text-gray-700 mb-1">Situación socioeconómica </label>
        <textarea name="situacion_socioeconomica" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Situación familiar</label>
        <textarea name="situacion_familiar" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Situación medica </label>
        <textarea  name="situacion_medica"class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Situación escolar </label>
        <textarea name="situacion_escolar" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Actividades extraescolares </label>
        <textarea name="actividades_extraescolares" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Intervenciones externas</label>
        <textarea name="intervenciones_externas" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Antecedentes</label>
        <textarea name="antecedentes" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>
        
        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
        <textarea name="observaciones" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2"></textarea>      
    </div>

    <div class="space-y-8">
        <p class="separador">Documentación</p>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button class="btn-subir">Examinar</button>
                    <span class="text-sm text-gray-500">Solo archivos en formato pdf, jpeg, png o doc con menos de 100Kb</span>
                </div>
            </div>
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-700">Cargados:</p>
                <div class="space-y-2">
                    {{-- Placeholder para archivos cargados --}}
                    <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                        <span class="text-sm text-gray-600">Documento1.pdf</span>
                        <button class="text-gray-500 hover:text-red-500">
                            {{-- SVG Icon --}}
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                        <span class="text-sm text-gray-600">Documento2.pdf</span>
                        <button class="text-gray-500 hover:text-red-500">
                            {{-- SVG Icon --}}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <div class="fila-botones mt-8" x-show="true">
        <button type="submit" class="btn-aceptar">Guardar</button>
        <button class="btn-eliminar" >Desactivar</button>
        <a class="btn-volver" href="{{ route('alumnos.principal') }}" >Volver</a>
    </div>
</form>
@endsection
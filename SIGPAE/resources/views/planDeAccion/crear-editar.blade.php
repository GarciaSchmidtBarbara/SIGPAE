

@extends('layouts.base')
@section('encabezado', 'Plan de acción N°')


@section('contenido')
{{-- 1. CONTENEDOR PRINCIPAL x-data --}}
{{-- Inicializamos la variable que contendrá el valor seleccionado --}}
<div x-data="{ tipoPlanSeleccionado: 'Institucional' }" class="space-y-6">

    <div class="space-y-8">
        <p>Estado: Abierto </p>
    </div>

    <div class="space-y-8">
        <p class="separador">Tipo</p>
        @php
            $tipoPlan = ['Institucional', 'Individual', 'Grupal'];
        @endphp

        <div class="space-y-4">
            {{-- 2. LLAMADA AL COMPONENTE --}}
            <x-opcion-unica
                :items="$tipoPlan"
                name="tipo_plan" 
                layout="horizontal"
                
                {{-- PASAMOS EL MODELO DE ALPINE AL COMPONENTE --}}
                x-model="tipoPlanSeleccionado"
            />
        </div>

    </div>

    {{-- 3. BLOQUES DEPENDIENTES CON x-show --}}

    <!-- DESTINATARIO (Visible solo si es 'Individual') -->
    {{-- AÑADIDO: style="display: none;" para prevenir que se muestre antes de que Alpine cargue --}}
    <div id="destinatario" 
         x-show="tipoPlanSeleccionado === 'Individual'" 
         style="display: none;">
        <div class="space-y-10 mb-6">
            <p class="separador">Destinatario</p>
            <div class="fila-botones">
                <button class="btn-aceptar">Alumno busqueda</button>
                <button class="btn-aceptar">Documento busqueda</button>
            </div>
            <div class="space-y-2">
                <p class="font-semibold">Información personal del alumno</p>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                      <p><span class="font-semibold">Nombre y Apellido:</span> Juan Pérez</p>
                      <p><span class="font-semibold">Nacionalidad:</span> Argentina</p>
                    </div>
                    <div>
                      <p><span class="font-semibold">DNI:</span> 12345678</p>
                      <p><span class="font-semibold">Domicilio:</span> Calle Falsa 123</p>
                    </div>
                    <div>
                      <p><span class="font-semibold">Fecha de nacimiento:</span> 01/01/2000</p>
                      <p><span class="font-semibold">Edad:</span> 10</p>
                    </div>
                    <div>
                      <p><span class="font-semibold">Curso:</span> 3°A</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- DESTINATARIOS (Visible solo si es 'Grupal') -->
    {{-- AÑADIDO: style="display: none;" para prevenir que se muestre antes de que Alpine cargue --}}
    <div id="destinatarios" 
         x-show="tipoPlanSeleccionado === 'Grupal'" 
         style="display: none;">
        <div class="space-y-10 mb-6">
            <p class="separador">Destinatario</p>
            <div class="fila-botones">
                <button class="btn-aceptar">Alumno busqueda</button>
                <button class="btn-aceptar">Documento busqueda</button>
            </div>
            <div class="space-y-2">
                <p class="font-semibold">Información personal de los alumnos</p>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre y Apellido
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            DNI
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha nacimiento
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Curso
                        </th>
                        <th class="px-4 py-2 w-10">
                            {{-- Columna para el ícono de eliminar --}}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- 
                       Aquí iría un bucle @foreach para listar los alumnos ya cargados.
                       Por ahora, dejamos filas de ejemplo.
                    --}}
                    
                    {{-- Fila de ejemplo 1 --}}
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Juan Pérez</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">12345678</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">01/01/2000</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">3°A</td>
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
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Ana Gómez</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">98765432</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">15/05/2001</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">3°A</td>
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
        </div>
            </div>
        </div>
    </div>

    
    
    <!-- Campo de texto (Común/Always) -->
    {{-- NO necesita display: none. Se muestra siempre. --}}
    <div id="campo-texto" x-show="true">
        <p class="separador">Descripcion</p>
        <div class="space-y-2 mb-6">
            {{-- Contenido del campo-texto --}}
            <label class="block text-sm font-medium text-gray-700">Objetivos <span class="text-red-500">*</span></label>
            <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" name="objetivos" rows="4"></textarea>
            <label class="block text-sm font-medium text-gray-700">Acciones a realizar <span class="text-red-500">*</span></label>
            <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" name="acciones" rows="2"></textarea>
            <label class="block text-sm font-medium text-gray-700">Observaciones <span class="text-red-500">*</span></label>
            <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" name="observaciones" rows="2"></textarea>

            {{-- Bloque de Documentos --}}
            <label class="block text-base font-medium text-gray-700">Documentos</label>
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
    </div>

    <!-- RESPONSABLES (Visible si es 'Individual' O 'Grupal') -->
    {{-- AÑADIDO: style="display: none;" --}}
    <div id="responsables" 
         x-show="tipoPlanSeleccionado === 'Individual' || tipoPlanSeleccionado === 'Grupal'"
         style="display: none;">
        <div class="space-y-10 mb-6">
            <p class="separador">Responsables</p>
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700">Profesionales <span class="text-red-500">*</span></label>
                <div class="fila-botones">
                    <button class="btn-aceptar">Buscar profesional</button>
                    <button class="btn-aceptar">Agregar profesional</button>
                </div>
                <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                  <span class="text-sm text-gray-600">Profesional 1</span>
                  <button class="text-gray-500 hover:text-red-500">
                      {{-- SVG Icon --}}
                  </button>
                </div>
                <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                  <span class="text-sm text-gray-600">Profesional 2</span>
                  <button class="text-gray-500 hover:text-red-500">
                      {{-- SVG Icon --}}
                  </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción (Común/Always) -->
    <div class="fila-botones mt-8" x-show="true">
        <button class="btn-aceptar">Marcar como cerrado</button>
        <button class="btn-aceptar">Guardar</button>
        <button class="btn-eliminar" >Eliminar</button>
        <a class="btn-volver" href="{{ route('planDeAccion.principal') }}" >Volver</a>
    </div>
</div>
@endsection

{{-- IGNORE ESTO
 PARA CUANDO ESTE EL MODELO Y CONTROLADOR
  Determinar si el plan de acción está cerrado 
 <button class="btn-aceptar" {{ $esCerrado ? 'disabled' : '' }}>Guardar</button>
    <button class="btn-eliminar" {{ $esCerrado ? 'disabled' : '' }}>Eliminar</button>
    
    Mantener visible solo botones de acción final si aplica
    @if ($planDeAccion->estado === 'Cerrado')
        <a class="btn-volver" href="...">Imprimir</a>
    @endif
--}}
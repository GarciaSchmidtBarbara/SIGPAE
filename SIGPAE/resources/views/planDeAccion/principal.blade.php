@extends('layouts.base')

@section('encabezado', 'Planes de acción')

@section('contenido')

    <div class="fila-botones mt-8">
        <a class="btn-aceptar" href="{{ route('planDeAccion.crear-editar') }}">Crear</a>
        <button class="btn-buscar">Tipo</button>
        <button class="btn-buscar">Estado</button>
        <button class="btn-buscar">Curso</button>
        <button class="btn-buscar">Alumno</button>
    </div>    

    <div class="space-y-10 mb-6 mt-6">
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha nacimiento
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Destinatarios
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Responsables
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
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Activo</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Grupal</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">12/03/1985</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">2°B</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">María González</td>
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
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Inactivo</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Individual</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">27/08/1979</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Lucas Rearte</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Carlos Méndez</td>
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

@endsection
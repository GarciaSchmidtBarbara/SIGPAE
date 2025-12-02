@extends('layouts.base')

@section('encabezado', 'Gestión de Planillas y Actas')

@section('contenido')
    
    {{-- BARRA DE HERRAMIENTAS (Buscador y Crear) --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        {{-- 1. BUSCADOR --}}
        <form action="{{ route('planillas.principal') }}" method="GET" class="w-full md:w-1/2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    {{-- Icono Lupa --}}
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    type="search" 
                    name="buscar" 
                    value="{{ request('buscar') }}"
                    class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" 
                    placeholder="Buscar por nombre, tipo, escuela o grado..." 
                >
            </div>
        </form>

        {{-- 2. BOTÓN CREAR (Tu lógica Alpine intacta pero estilizada) --}}
        <div x-data="{ abrirPlanilla:false, tipo:'' }" x-cloak>
            <button @click="abrirPlanilla = true" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-md transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Nueva Planilla</span>

            </button>

            <div class="flex justify-end mb-2">
                <a href="{{ route('planillas.papelera') }}" class="text-sm text-gray-500 hover:text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Ver Papelera
                </a>
            </div>

            {{-- MODAL DE SELECCIÓN (Tu código original mejorado visualmente) --}}
            <x-ui.modal x-data="{ open: false }"
                x-effect="open = abrirPlanilla; if (!open && abrirPlanilla) abrirPlanilla = false" @click.stop
                title="Seleccione el tipo de documento" size="lg" :closeOnBackdrop="true">

                <div class="p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento:</label>
                    <select x-model="tipo" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3">
                        <option value="" disabled selected>— Seleccione una opción —</option>
                        <option value="acta-trabajo">📄 Acta Reunión de Trabajo (EI)</option>
                        <option value="acta-equipo">👥 Acta Equipo Interdisciplinario (Directivos)</option>
                        <option value="acta-banda">🏫 Acta Reunión Banda (Completa)</option>
                        <option value="planilla-medial">📊 Planilla Medial</option>
                        <option value="planilla-final">🎓 Planilla Final</option>
                    </select>
                </div>

                <x-slot:footer>
                    <div class="w-full flex justify-end gap-3 p-2 bg-gray-50 rounded-b-lg">
                        <button class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" @click="abrirPlanilla = false">
                            Cancelar
                        </button>
                        <button class="px-4 py-2 text-white rounded-md transition-colors"
                            :class="tipo ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                            :disabled="!tipo" @click="
                                    if (tipo === 'acta-trabajo')   { window.location = '{{ route('planillas.acta-reunion-trabajo.create') }}' }
                                    if (tipo === 'acta-equipo')    { window.location = '{{ route('planillas.acta-equipo-indisciplinario.create') }}' }
                                    if (tipo === 'acta-banda')     { window.location = '{{ route('planillas.acta-reuniones-banda.create') }}' }
                                    if (tipo === 'planilla-medial'){ window.location = '{{ route('planillas.planilla-medial.create') }}' }
                                    if (tipo === 'planilla-final') { window.location = '{{ route('planillas.planilla-final.create') }}' }
                                ">
                            Continuar &rarr;
                        </button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        @if($planillas->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-bold tracking-wider border-b border-gray-200">
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Nombre / Descripción</th>
                            <th class="p-4">Detalles (Escuela/Grado)</th>
                            <th class="p-4">Fecha Creación</th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($planillas as $item)
                            <tr class="hover:bg-indigo-50/30 transition duration-150 ease-in-out group">
                                
                                {{-- 1. TIPO (Con etiqueta de color) --}}
                                <td class="p-4">
                                    @php
                                        // Lógica para elegir color según el texto
                                        $esActa = Str::contains($item->tipo_planilla, 'ACTA');
                                        $colorClass = $esActa 
                                            ? 'bg-blue-100 text-blue-700 border-blue-200' 
                                            : 'bg-green-100 text-green-700 border-green-200';
                                        
                                        // Nombre corto para la etiqueta
                                        $nombreCorto = $esActa ? 'ACTA' : 'PLANILLA';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colorClass }}">
                                        {{ $nombreCorto }}
                                    </span>
                                </td>

                                <td class="p-4 align-middle">
                                    <div class="font-medium text-gray-900">
                                        {{-- ENLACE QUE QUERÍAS --}}
                                        <a href="{{ route('planillas.editar', $item->id_planilla) }}" class="hover:text-blue-600 hover:underline">
                                            {{ $item->nombre_planilla ?? 'Sin Nombre' }}
                                        </a>
                                    </div>
                                    {{-- Subtítulo gris --}}
                                    <div class="text-xs text-gray-500 mt-1 font-normal truncate max-w-xs">
                                        {{ Str::limit($item->tipo_planilla, 40) }}
                                    </div>
                                </td>

                                {{-- 2. NOMBRE COMPLETO --}}
                                <td class="p-4 font-medium text-gray-900">
                                    {{ $item->nombre_planilla ?? 'Sin Nombre' }}
                                    <div class="text-xs text-gray-500 mt-1 font-normal">
                                        {{ Str::limit($item->tipo_planilla, 40) }}
                                    </div>
                                </td>

                                {{-- 3. DETALLES (INTELIGENTE: Muestra lo que corresponda) --}}
                                <td class="p-4 text-sm text-gray-600">
                                    @if(isset($item->datos_planilla['escuela']))
                                        {{-- Es Planilla Medial/Final --}}
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2m7-2a2 2 0 01-2-2h-1"></path></svg>
                                            Esc: {{ $item->datos_planilla['escuela'] }}
                                        </div>
                                    @elseif(isset($item->datos_planilla['grado']))
                                        {{-- Es Acta --}}
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            Grado: {{ $item->datos_planilla['grado'] }}
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- 4. FECHA --}}
                                <td class="p-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ $item->created_at->format('d/m/Y') }}
                                    <span class="text-xs text-gray-400 ml-1">{{ $item->created_at->format('H:i') }}</span>
                                </td>

                                {{-- 5. ACCIONES --}}
                                <td class="p-4 text-center">
    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        
        {{-- 1. BOTÓN VER / EDITAR (Ojo) --}}
        <a href="{{ route('planillas.editar', $item->id_planilla) }}" class="text-gray-500 hover:text-blue-600 transition" title="Ver / Editar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </a>

        {{-- 2. BOTÓN ELIMINAR (Tacho) --}}
        <form action="{{ route('planillas.eliminar', $item->id_planilla) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-gray-500 hover:text-red-600 transition" title="Mover a Papelera" onclick="return confirm('¿Mover esta planilla a la papelera?')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </form>

    </div>
</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            <div class="p-4 border-t border-gray-200">
                {{ $planillas->links() }}
            </div>

        @else
            {{-- ESTADO VACÍO (Empty State) --}}
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay planillas creadas</h3>
                <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva planilla o acta desde el botón superior.</p>
            </div>
        @endif

    </div>
@endsection
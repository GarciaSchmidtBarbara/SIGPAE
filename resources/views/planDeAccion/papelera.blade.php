@extends('layouts.base')

@section('encabezado', 'Papelera de Reciclaje')

@section('contenido')
    <div class="max-w-6xl mx-auto mt-6 px-4">
        
        <div class="mb-4">
            <a href="{{ route('planDeAccion.principal') }}" class="text-blue-600 hover:underline flex items-center gap-2">
                &larr; Volver al listado principal
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border border-red-100">
            
            <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Elementos eliminados
            </h3>

            @if($borradas->isEmpty())
                <p class="text-gray-500 italic">La papelera está vacía.</p>
            @else
                {{-- DESKTOP TABLE --}}
                <div class="hidden md:block">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-red-50 text-red-800 uppercase text-xs font-bold border-b border-red-200">
                                <th class="p-3">Nombre</th>
                                <th class="p-3">Fecha</th>
                                <th class="p-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100">
                            @foreach($borradas as $item)
                                <tr>
                                    <td class="p-3 font-medium text-gray-700">
                                        {{ $item->descripcion }}
                                        <div class="text-xs text-gray-500">
                                            {{ $item->tipo_plan->value }}
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-500">
                                        {{ $item->deleted_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <x-papelera-acciones 
                                            :id="$item->id_plan_de_accion"
                                            restoreRoute="planDeAccion.restaurar"
                                            destroyRoute="planDeAccion.destruir"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS --}}
                <div class="md:hidden space-y-4">
                    @foreach($borradas as $item)
                        <div class="border border-red-100 rounded-lg p-4 shadow-sm bg-red-50">
                            <div class="font-semibold text-gray-800">
                                {{ $item->descripcion }}
                            </div>

                            <div class="text-xs text-gray-600 mt-1">
                                Tipo: {{ $item->tipo_plan->value }}
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Eliminado: {{ $item->deleted_at->format('d/m/Y H:i') }}
                            </div>

                            <div class="flex gap-4 mt-3">
                                <x-papelera-acciones 
                                            :id="$item->id_plan_de_accion"
                                            restoreRoute="planDeAccion.restaurar"
                                            destroyRoute="planDeAccion.destruir"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
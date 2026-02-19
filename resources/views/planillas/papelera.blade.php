@extends('layouts.base')

@section('encabezado', 'Papelera de Reciclaje')

@section('contenido')
    <div class="max-w-6xl mx-auto mt-6 px-4">
        
        <div class="mb-4">
            <a href="{{ route('planillas.principal') }}" class="text-blue-600 hover:underline flex items-center gap-2">
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
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-red-50 text-red-800 uppercase text-xs font-bold border-b border-red-200">
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Fecha de Eliminación</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-100">
                        @foreach($borradas as $item)
                            <tr>
                                <td class="p-3 font-medium text-gray-700">
                                    {{ $item->nombre_planilla }}
                                    <div class="text-xs text-gray-500">{{ $item->tipo_planilla }}</div>
                                </td>
                                <td class="p-3 text-sm text-gray-500">
                                    {{ $item->deleted_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="p-3 text-center flex justify-center gap-3">
                                    
                                    {{-- RESTAURAR --}}
                                    <form action="{{ route('planillas.restaurar', $item->id_planilla) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 font-bold text-sm flex items-center gap-1" title="Restaurar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Restaurar
                                        </button>
                                    </form>

                                    {{-- ELIMINAR DEFINITIVO --}}
                                    <form action="{{ route('planillas.destruir', $item->id_planilla) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-bold text-sm flex items-center gap-1" onclick="return confirm('¿Estás SEGURO? Esto no se puede deshacer.')" title="Borrar para siempre">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Eliminar
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
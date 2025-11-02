@props([
    'columnas' => [], // Ej: ['nombre', 'apellido']
    'filas' => [],    // Array de objetos o arrays
    'acciones' => [], // Ej: ['ver' => 'ruta.ver', 'eliminar' => 'ruta.eliminar']
    'idCampo' => 'id', // Campo que identifica la fila
])

<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-100">
        <tr>
            @foreach ($columnas as $col)
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                    {{ is_array($col) ? $col['label'] : ucfirst($col) }}
                </th>
            @endforeach
            @if(!empty($acciones))
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Acciones</th>
            @endif
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($filas as $fila)
            <tr>
                @foreach ($columnas as $col)
                    @php
                        $key = is_array($col) ? $col['key'] : $col;
                    @endphp
                    <td class="px-4 py-2 text-sm text-gray-900">
                        {{ data_get($fila, $key, '—') }}
                    </td>
                @endforeach
                
                @if(!empty($acciones))
                    <td class="px-4 py-2 text-sm flex gap-2">
                        @if(isset($acciones['ver']))
                            <a href="{{ route($acciones['ver'], $fila[$idCampo]) }}" class="text-blue-600 hover:underline">Ver</a>
                        @endif

                        @if(isset($acciones['editar']))
                            <a href="{{ route($acciones['editar'], $fila[$idCampo]) }}" class="text-yellow-600 hover:underline">Editar</a>
                        @endif

                        @if(isset($acciones['eliminar']))
                            <form action="{{ route($acciones['eliminar'], $fila[$idCampo]) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">
                                    {{ $acciones['eliminar_label'] ?? 'Eliminar' }}
                                </button>
                            </form>
                        @endif
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columnas) + 1 }}" class="px-4 py-2 text-center text-gray-500">
                    No hay registros disponibles.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

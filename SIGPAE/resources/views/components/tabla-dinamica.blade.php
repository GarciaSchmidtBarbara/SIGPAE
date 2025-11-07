@props([
    'columnas' => [],
    'filas' => [],
    'idCampo' => 'id',
    'acciones' => null, {{-- <-- se agrega esta prop --}}
])

<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-100">
        <tr>
            @foreach ($columnas as $col)
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                    {{ is_array($col) ? $col['label'] : ucfirst($col) }}
                </th>
            @endforeach
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Acciones</th>
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

                <td class="px-4 py-2 text-sm flex gap-2">
                    {{-- Si se pasó una función, se ejecuta --}}
                    @if (is_callable($acciones))
                        {!! $acciones($fila) !!}
                    @endif
                </td>
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

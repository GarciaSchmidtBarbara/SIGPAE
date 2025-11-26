@props([
    'columnas' => [],
    'filas' => [],
    'idCampo' => 'id',
    'acciones' => null, 
    'formatters' => [], //para pasarle "si, no". Formatos personalizados
    'filaEnlace' => null,
])

<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-100">
        <tr>
            @foreach ($columnas as $col)
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                    {{ is_array($col) ? $col['label'] : ucfirst($col) }}
                </th>
            @endforeach
            @if (is_callable($acciones))
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ACCIONES</th>
            @endif
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($filas as $fila)
            <tr
                @if($filaEnlace)
                    onclick="window.location='{{ $filaEnlace($fila) }}'"
                    class="cursor-pointer hover:bg-gray-100 transition"
                @endif>
                @foreach ($columnas as $col)
                    @php
                            $key = is_array($col) ? $col['key'] : $col;

                            // Si hay formatter, se usa directamente con el valor (aunque no exista el key)
                            if (isset($col['formatter']) && is_callable($col['formatter'])) {
                                $valor = $col['formatter'](data_get($fila, $key), $fila);
                            } elseif (isset($formatters[$key]) && is_callable($formatters[$key])) {
                                $valor = $formatters[$key](data_get($fila, $key), $fila);
                            } else {
                                $valor = data_get($fila, $key, '—');
                            }
                            //Conversión: si el valor es un Enum, convertirlo a string
                            if ($valor instanceof \BackedEnum) {
                                $valor = method_exists($valor, 'label') ? $valor->label() : $valor->value;
                            }
                        @endphp
                    <td class="px-4 py-2 text-sm text-gray-900">
                        {!! $valor !!}
                    </td>
                @endforeach

                {{-- Si se pasó una función, se ejecuta --}}
                @if (is_callable($acciones))
                    <td class="px-4 py-2 text-sm flex gap-2">
                        {!! $acciones($fila) !!}
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columnas) + (is_callable($acciones) ? 1 : 0) }}" class="px-4 py-2 text-center text-gray-500">
                    No hay registros disponibles.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

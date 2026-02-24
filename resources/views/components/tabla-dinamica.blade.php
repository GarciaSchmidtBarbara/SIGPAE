@props([
    'columnas' => [],
    'filas' => [],
    'idCampo' => 'id',
    'acciones' => null, 
    'formatters' => [], //para pasarle "si, no". Formatos personalizados
    'filaEnlace' => null,
])

<table  class="modern-table">
    <thead class="bg-gray-100">
        <tr>
            @foreach ($columnas as $col)
                <th>
                    {{ is_array($col) ? $col['label'] : ucfirst($col) }}
                </th>
            @endforeach
            @if (is_callable($acciones))
                <th>ACCIONES</th>
            @endif
        </tr>
    </thead>
    <tbody>
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
                    <td>
                        {!! $valor !!}
                    </td>
                @endforeach

                {{-- Si se pasó una función, se ejecuta --}}
                @if (is_callable($acciones))
                    <td>
                        {!! $acciones($fila) !!}
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columnas) + (is_callable($acciones) ? 1 : 0) }}">
                    No hay registros disponibles.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

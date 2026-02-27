@props([
    'columnas' => [],
    'filas' => [],
    'idCampo' => 'id',
    'acciones' => null, 
    'formatters' => [], //para pasarle "si, no". Formatos personalizados
    'filaEnlace' => null,
])
{{-- TABLA MOBILE GENÉRICA --}}
<div class="md:hidden overflow-x-auto">
    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
            <tr>
                @foreach(array_slice($columnas, 0, 3) as $col)
                    <th class="px-3 py-2 text-left">
                        {{ is_array($col) ? $col['label'] : ucfirst($col) }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody class="divide-y">
            @forelse ($filas as $fila)
                <tr  @if($filaEnlace)
                    data-href="{{ $filaEnlace($fila) }}"
                    class="cursor-pointer hover:bg-gray-50 transition fila-click"
                @else
                    class="hover:bg-gray-50"
                @endif>
                    @foreach(array_slice($columnas, 0, 3) as $col)

                        @php
                            $key = is_array($col) ? $col['key'] : $col;

                            if (isset($col['formatter']) && is_callable($col['formatter'])) {
                                $valor = $col['formatter'](data_get($fila, $key), $fila);
                            } elseif (isset($formatters[$key]) && is_callable($formatters[$key])) {
                                $valor = $formatters[$key](data_get($fila, $key), $fila);
                            } else {
                                $valor = data_get($fila, $key, '—');
                            }

                            if ($valor instanceof \BackedEnum) {
                                $valor = method_exists($valor, 'label')
                                    ? $valor->label()
                                    : $valor->value;
                            }
                        @endphp

                        <td class="px-3 py-2">
                            {!! $valor !!}
                        </td>

                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">
                        No hay registros disponibles.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{--desktop, tabla tradicional--}}
<div class="hidden md:block overflow-x-auto">
    <table  class="modern-table text-sm">
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
</div>

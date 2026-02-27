@props([
    'layout' => 'vertical',
    'items' => [],
    'name' => '',
    'seleccion' => null,
])

<div class="{{ $layout === 'horizontal' 
    ? 'flex gap-4 flex-wrap items-center' 
    : 'space-y-2' }}">

    @foreach ($items as $item)
        <label class="flex items-center gap-2 cursor-pointer">
             <input type="radio"
                   name="{{ $name }}"
                   value="{{ $item }}"
                   class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                   {{ old($name, $seleccion) == $item ? 'checked' : '' }}
                   {{ $attributes->whereStartsWith('x-model') }}
            >
            <span class="text-sm text-gray-700">{{ $item }}</span>
        </label>

    @endforeach
</div>
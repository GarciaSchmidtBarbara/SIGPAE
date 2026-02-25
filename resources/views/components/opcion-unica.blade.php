@props([
    'layout' => 'vertical',
    'items' => [],
    'name' => '',
    'seleccion' => null,
])

<div class="custom-radio-group {{ $layout === 'horizontal' ? 'flex gap-4 flex-wrap items-center' : 'space-y-2' }}">
    @foreach ($items as $item)
        <label class="flex items-center gap-2 cursor-pointer {{ $layout === 'horizontal' ? 'min-w-[100px]' : '' }}">
            <input type="radio"
                   class="custom-radio"
                   name="{{ $name }}"
                   value="{{ $item }}"
                   {{ old($name, $seleccion) == $item ? 'checked' : '' }}
                   
                   {{ $attributes->whereStartsWith('x-model') }} 
                   >
            <span class="text-gray-700">{{ $item }}</span>
        </label>
    @endforeach
</div>
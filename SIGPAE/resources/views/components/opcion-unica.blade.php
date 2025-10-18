@props([
    'layout' => 'vertical',
    'items' => [],
    'name' => ''
])

<div class="custom-radio-group {{ $layout === 'horizontal' ? 'flex flex-wrap gap-4' : 'space-y-2' }}">
    @foreach ($items as $item)
        <label class="flex items-center gap-2 cursor-pointer {{ $layout === 'horizontal' ? 'min-w-[150px]' : '' }}">
            <input type="radio"
                   class="custom-radio"
                   name="{{ $name }}"
                   value="{{ $item }}"
                   {{ old($name) === $item ? 'checked' : '' }}>
            <span class="text-gray-700">{{ $item }}</span>
        </label>
    @endforeach
</div>

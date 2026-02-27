@props([
    'layout' => 'vertical',
    'items' => [],
    'name' => ''
])

<div class="{{ $layout === 'horizontal' 
? 'flex flex-wrap gap-4 items-center' 
: 'space-y-2' }}">
    @foreach ($items as $item)
        <label class="flex items-center gap-2 cursor-pointer {{ $layout === 'horizontal' ? 'min-w-[140px]' : '' }}">
            <input type="checkbox"
                   name="{{ $name }}[]"
                   value="{{ $item }}"
                   {{ in_array($item, old($name, [])) ? 'checked' : '' }}>
                   class= "w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                   {{ in_array($item, old($name, [])) ? 'checked' : '' }}
                   {{ $attributes }}
            >
            <span class="text-sm text-gray-700">{{ $item }}</span>
        </label>
    @endforeach
</div>

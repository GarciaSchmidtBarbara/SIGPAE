@props([
    'label' => '',                 
    'name' => '',                  
    'value' => null,               
    'required' => false,           
    'placeholder' => '',           
    'class' => 'w-1/5',          
])

<div class="flex flex-col {{ $class }}">
    <label for="{{ $name }}" class="text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="date"
        placeholder="{{ $placeholder ?: $label }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge([
            'class' => 'border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500'
        ]) }}
    >
</div>

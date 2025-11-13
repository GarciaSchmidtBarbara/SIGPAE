@props([
    'text' => '',
    'required' => false,
])

<p class="text-sm font-medium text-gray-700 mb-1">
    {{ $text }}
    @if ($required)
        <span class="text-red-500">*</span>
    @endif
</p>

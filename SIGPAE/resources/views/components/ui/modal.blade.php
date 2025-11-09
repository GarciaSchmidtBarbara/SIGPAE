@props([
    'title' => '',          // título del header (si está vacío, no se muestra el header)
    'size' => 'md',         // sm | md | lg | xl
    'closeOnBackdrop' => true,
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
    $panelMax = $sizes[$size] ?? $sizes['md'];
@endphp

<div
   {{ $attributes }}
    x-cloak
    x-show="open"
    x-transition.opacity
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-[100] flex items-center justify-center"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        class="fixed inset-0 bg-black/50"
        x-show="open"
        x-transition.opacity
        @if($closeOnBackdrop) @click="open = false" @endif
    ></div>

    {{-- Panel --}}
    <div
        x-show="open"
        x-transition.scale
        class="relative z-[110] w-full {{ $panelMax }} rounded-2xl bg-white shadow-xl"
    >
        {{-- Header (solo si hay título) --}}
        @if(filled($title))
            <div class="flex items-start justify-between gap-4 border-b px-5 py-4">
                <h2 class="text-lg font-semibold">{{ $title }}</h2>
                <button
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100"
                    @click="open = false"
                    aria-label="Cerrar"
                >✕</button>
            </div>
        @endif

        {{-- Body --}}
        <div class="px-5 py-4">
            {{ $slot }}
        </div>

        {{-- Footer (opcional) --}}
        @isset($footer)
            <div class="flex justify-end gap-2 border-t px-5 py-4">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>

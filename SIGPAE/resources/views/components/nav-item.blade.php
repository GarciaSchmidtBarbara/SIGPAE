@props([
    'route' => null,          // nombre de la ruta (p.ej. 'welcome')
    'params' => [],           // params de la ruta (p.ej. ['id' => 1])
    'label' => null,          // texto del link (opcional si usás <x-slot>)
    'icon' => null,           // nombre del componente de icono (p.ej. 'icons.icono-home')
    'exact' => false,         // si el activo debe ser coincidencia exacta
    'activeWhen' => null,     // forzar/override condición activo (bool)
    'class' => '',            // clases extra
])

@php
    $url = $route ? route($route, $params) : ($href ?? '#');

    // determina "activo"
    $computedActive =
        !is_null($activeWhen)
            ? (bool) $activeWhen
            : ($route
                ? ( $exact
                    ? request()->routeIs($route)
                    : request()->routeIs($route . '*')
                  )
                : false);

    $baseClasses = 'flex items-center w-full px-4 py-3 mb-2 text-white transition-colors duration-200 rounded-lg hover:bg-white/10 group';
    $activeClass = $computedActive ? 'bg-white/20 shadow-inner' : '';
    $classes = trim("$baseClasses $activeClass $class");

@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        {{-- Renderiza cualquier componente como icono --}}
        <div class="mr-3 w-6 flex justify-center text-lg opacity-90 group-hover:opacity-100">
        <x-dynamic-component :component="$icon" />
        </div>
    @endif

    <span class="font-medium tracking-wide">{{ $label ?? $slot }}</span>
</a>

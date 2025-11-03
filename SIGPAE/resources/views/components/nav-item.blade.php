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

    // clases base + activo
    $baseClasses = 'links flex items-center';
    $activeClass = $computedActive ? 'activo' : '';

    // mergea con $attributes->merge() para permitir pasar más attrs/clases desde afuera
    $classes = trim("$baseClasses $activeClass $class");
@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        {{-- Renderiza cualquier componente como icono --}}
        <x-dynamic-component :component="$icon" class="mr-2" />
    @endif

    <span>{{ $label ?? $slot }}</span>
</a>

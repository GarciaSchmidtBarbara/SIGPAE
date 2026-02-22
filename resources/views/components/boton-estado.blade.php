@props([
    'activo',
    'route', 
    'text_activo' => null,  // Texto a mostrar si está activo
    'text_inactivo' => null  // Texto a mostrar si está inactivo
])

@php
    //valor por defecto ('Activar'/'Desactivar')
    $textoDesactivar = $text_activo ?? 'Desactivar';
    $textoActivar = $text_inactivo ?? 'Activar';
@endphp

@if($activo)
    {{-- ACTIVO → mostrar icono basura --}}
    <form action="{{ $route }}" method="POST" onsubmit="return confirm('¿Confirmar desactivación? El alumno pasará a estado inactivo.')">
        @csrf
        @method('PATCH')

        <button type="submit"
                class="text-gray-500 hover:text-red-600 transition flex justify-center w-full"
                title="{{ $textoDesactivar }}">

            <x-icons.icono-eliminar class="w-5 h-5" />
        </button>
    </form>

@else
    {{-- INACTIVO → botón verde normal --}}
    <form action="{{ $route }}" method="POST">
        @csrf
        @method('PUT')

        <button type="submit"
                @click.stop
                class="px-3 py-1 rounded text-white bg-green-500 hover:bg-green-600 transition">
            {{ $textoActivar }}
        </button>
    </form>

@endif

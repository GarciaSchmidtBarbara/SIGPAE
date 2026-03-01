@props([
    'activo',
    'route',
    'text_activo' => null,
    'text_inactivo' => null,
    'message_activo' => null,
    'message_inactivo' => null,
    'redirect_before_submit' => null,
    'id' => uniqid('estado_')
])

@php
    $textoDesactivar = $text_activo ?? 'Desactivar';
    $textoActivar = $text_inactivo ?? 'Activar';

    $mensajeDesactivar = $message_activo ?? '¿Confirmar desactivación?';
    $mensajeActivar = $message_inactivo ?? '¿Confirmar activación?';

    $esActivo = (bool) $activo;

    $texto = $esActivo ? $textoDesactivar : $textoActivar;
    $mensaje = $esActivo ? $mensajeDesactivar : $mensajeActivar;

    $routeAfterClose = $route_after_close ?? null;

@endphp

<div class="flex items-center justify-center">

    {{-- FORM OCULTO --}}
    <form id="{{ $id }}"
          action="{{ $route }}"
          method="POST">
        @csrf
        @method('PATCH')
    </form>

    {{-- BOTÓN --}}
    <button type="button"
        title="{{ $texto }}"
        class="{{ $esActivo 
            ? 'text-gray-500 hover:text-red-600 transition flex justify-center w-full'
            : 'px-3 py-1 rounded text-white bg-green-500 hover:bg-green-600 transition'
        }}"
        @click.stop="
            @if($redirect_before_submit && $esActivo)
                window.location = '{{ $redirect_before_submit }}'
            @else
                $dispatch('abrir-modal-confirmar', { 
                    formId: '{{ $id }}',
                    message: '{{ $mensaje }}'
                })
            @endif
        "
    >
        @if($esActivo)
            <x-icons.icono-cerrar class="w-5 h-5" />
        @else
            <x-icons.icono-abrir class="w-5 h-5" />
        @endif
    </button>

</div>
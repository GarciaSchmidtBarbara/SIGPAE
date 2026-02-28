@props([
    'id',
    'restoreRoute',
    'destroyRoute'
])

<div class="flex items-center gap-2">

    {{-- RESTAURAR --}}
    <form action="{{ route($restoreRoute, $id) }}" method="POST">
        @csrf
        @method('PUT')
        <button type="submit"
                class="text-green-600 hover:text-green-800 text-sm font-medium">
            Restaurar
        </button>
    </form>

    {{-- ELIMINAR DEFINITIVO --}}
    <form action="{{ route($destroyRoute, $id) }}" 
          method="POST"
          onsubmit="return confirm('¿Eliminar definitivamente este registro?')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="text-red-600 hover:text-red-800 text-sm font-medium">
            Eliminar
        </button>
    </form>

</div>
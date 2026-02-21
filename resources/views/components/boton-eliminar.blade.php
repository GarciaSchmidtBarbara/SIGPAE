@props([
    'route',
    'id' => uniqid('delete_')
])

<div x-data="{ open: false }" @click.stop class="flex items-center justify-center">
    
    <form id="{{ $id }}"
          action="{{ $route }}"
          method="POST">
        @csrf
        @method('DELETE')
    </form>

    <button type="button"
        class="text-gray-500 hover:text-red-600 transition flex justify-center w-full"
        title="Eliminar"
        @click="$dispatch('abrir-modal-eliminar', { route: '{{ $route }}' })">
        <x-icons.icono-eliminar class="w-5 h-5" />
    </button>

</div>
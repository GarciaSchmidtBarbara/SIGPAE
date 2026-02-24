@props([
    'route',
    'message' => '¿Estás seguro?',
    'id' => uniqid('delete_')
])

<div class="flex items-center">
    
    <form id="{{ $id }}"
          action="{{ $route }}"
          method="POST">
        @csrf
        @method('DELETE')
    </form>

    <button type="button"
        class="text-gray-500 hover:text-red-600 transition flex justify-center w-full"
        title="Eliminar"
        @click.stop="$dispatch('abrir-modal-confirmar', { 
            formId: '{{ $id }}',
            message: '{{ $message }}'
        })">
        <x-icons.icono-eliminar class="w-5 h-5" />
    </button>

</div>
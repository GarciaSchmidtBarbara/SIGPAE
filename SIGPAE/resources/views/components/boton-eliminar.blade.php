@props(['texto' => 'Eliminar'])

<form action="{{ $route }}"
      method="POST"
      onclick="event.stopPropagation()"
      onsubmit="event.stopPropagation(); return confirm('¿Seguro que querés eliminar esta intervención?');">
    @csrf
    @method('DELETE')

    <button type="submit" class="btn-eliminar">
        {{ $texto ?? 'Eliminar' }}
    </button>
</form>




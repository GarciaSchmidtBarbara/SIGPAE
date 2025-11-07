@props(['activo', 'route'])

<form action="{{ $route }}" method="POST">
    @csrf
    <button 
        type="submit"
        class="px-3 py-1 rounded text-white {{ $activo ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
        {{ $activo ? 'Desactivar' : 'Activar' }}
    </button>
</form>

@props([
    'activo',
    'route', 
    'text_activo' => null,  // Texto a mostrar si está activo
    'text_inactivo' => null  // Texto a mostrar si está inactivo
])

@php
    //valor por defecto ('Activar'/'Desactivar')
    $textoBoton = $activo 
        ? ($text_activo ?? 'Desactivar') 
        : ($text_inactivo ?? 'Activar');
        
    // Definir la clase
    $claseBoton = $activo 
        ? 'bg-red-500 hover:bg-red-600' // Rojo para desactivar/cerrar
        : 'bg-green-500 hover:bg-green-600'; // Verde para activar/abrir
@endphp

<form action="{{ $route }}" method="POST">
    @csrf
    @method('PUT') 
    
    <button 
        type="submit"
        class="px-3 py-1 rounded text-white {{ $claseBoton }}">
        {{ $textoBoton }}
    </button>
</form>

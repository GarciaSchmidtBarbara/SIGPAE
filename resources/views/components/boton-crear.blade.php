<button {{ $attributes->merge(['class' => 'btn-aceptar']) }}>
    {{ $slot }}
</button>

<!-- AL CREAR UN COMPONENTE DEBEN CREAR LA CLASE CSS EN BASE -->
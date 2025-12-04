<div class="flex items-center gap-2 justify-center">
    <button type="button" 
            @click="confirmarEliminar({{ $evento->id_evento }})"
            class="text-red-600 hover:text-red-800">
        <i class="fas fa-trash"></i>
    </button>
</div>

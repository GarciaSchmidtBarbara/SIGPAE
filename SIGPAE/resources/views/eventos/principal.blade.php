@extends('layouts.base')

@section('encabezado', 'Todos los eventos')

@section('contenido')
<div class="p-6" x-data="eventosData()">
    <!-- Botones de acción -->
    <div class="flex gap-2 mb-6">
        <a href="{{ route('eventos.crear') }}" class="btn-aceptar">Crear evento</a>
        <a href="{{ route('eventos.crear-derivacion') }}" class="btn-aceptar">Crear derivación externa</a>
    </div>

    <!-- Tabla de eventos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Tipo</th>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-center">Confirmación</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eventos as $evento)
                    @php
                        $profesionalActual = auth()->user()->getAuthIdentifier();
                        $esCreador = $evento->fk_id_profesional_creador == $profesionalActual;
                        $invitacion = $evento->esInvitadoA->firstWhere('fk_id_profesional', $profesionalActual);
                        $estaInvitado = $invitacion !== null;
                        $confirmado = $invitacion ? $invitacion->confirmacion : false;
                        $puedeConfirmar = $estaInvitado; // Solo invitados pueden confirmar
                    @endphp
                    <tr class="border-b hover:bg-gray-50 cursor-pointer" 
                        onclick="window.location='{{ $evento->tipo_evento?->value === 'DERIVACION_EXTERNA' ? route('eventos.editar-derivacion', $evento->id_evento) : route('eventos.ver', $evento->id_evento) }}'">
                        <td class="px-4 py-3">{{ $evento->tipo_evento?->value ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $evento->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            @if($puedeConfirmar)
                                <input type="checkbox" 
                                       {{ $confirmado ? 'checked' : '' }}
                                       @change="actualizarConfirmacion({{ $evento->id_evento }}, $event.target.checked)"
                                       class="h-4 w-4 rounded">
                            @else
                                <span class="text-gray-400 text-sm">{{ $esCreador ? 'Creador' : '-' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            <button type="button" 
                                    @click="confirmarEliminar({{ $evento->id_evento }})"
                                    class="text-red-600 hover:text-red-800 text-xl"
                                    title="Eliminar evento">
                                🗑️
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            No hay eventos registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div x-show="mostrarModal" 
         x-cloak
         x-transition.opacity
         @keydown.escape.window="mostrarModal = false"
         class="fixed inset-0 z-[100] flex items-center justify-center"
         role="dialog"
         aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50"
             @click="mostrarModal = false"></div>
        
        <!-- Panel -->
        <div class="relative z-[110] w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold mb-4">¿Confirmar eliminación?</h3>
                <p class="text-gray-700 mb-6">¿Está seguro que desea eliminar este evento?</p>
                
                <form id="formEliminarEvento" method="POST" :action="`{{ url('/eventos') }}/${eventoId}`">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="mostrarModal = false" class="btn-volver">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-eliminar">
                            Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function eventosData() {
    return {
        mostrarModal: false,
        eventoId: null,
        
        confirmarEliminar(id) {
            this.eventoId = id;
            this.mostrarModal = true;
        },
        
        async actualizarConfirmacion(eventoId, confirmado) {
            try {
                const response = await fetch(`/eventos/${eventoId}/confirmar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ confirmado })
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    alert('Error: ' + data.message);
                    // Revertir el checkbox
                    event.target.checked = !confirmado;
                }
            } catch (error) {
                console.error('Error al actualizar confirmación:', error);
                alert('Error al actualizar la confirmación');
                event.target.checked = !confirmado;
            }
        }
    };
}
</script>
@endsection

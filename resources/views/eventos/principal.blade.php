@extends('layouts.base')

@section('encabezado', 'Todos los eventos')

@section('contenido')
<div class="p-6" x-data="eventosData()" @abrir-modal-eliminar.window="abrir($event.detail)">
    <!-- Botones de acción y filtros -->
    <form method="GET" action="{{ route('eventos.principal') }}" class="flex gap-2 mb-6 flex-wrap items-center">
        <a href="{{ route('eventos.crear') }}" class="btn-aceptar">Crear evento</a>
        <a href="{{ route('eventos.crear-derivacion') }}" class="btn-aceptar">Crear derivación externa</a>

        <select name="tipo_evento" class="border px-2 py-1 rounded" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            @foreach($tiposEvento as $tipo)
                <option value="{{ $tipo->value }}" {{ request('tipo_evento') === $tipo->value ? 'selected' : '' }}>
                    {{ $tipo->label() }}
                </option>
            @endforeach
        </select>

    </form>

    <!-- Tabla de eventos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table  class="modern-table">
            <thead class="bg-gray-100">
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
                        $tipoColor = match($evento->tipo_evento?->value) {
                            'BANDA'             => 'bg-blue-100 text-blue-700 border-blue-200',
                            'RG'                => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'RD'                => 'bg-purple-100 text-purple-700 border-purple-200',
                            'CITA_FAMILIAR'     => 'bg-green-100 text-green-700 border-green-200',
                            'DERIVACION_EXTERNA'=> 'bg-amber-100 text-amber-700 border-amber-200',
                            default             => 'bg-gray-100 text-gray-600 border-gray-200',
                        };
                    @endphp
                    <tr class="border-b hover:bg-gray-50 cursor-pointer" 
                        onclick="window.location='{{ $evento->tipo_evento?->value === 'DERIVACION_EXTERNA' ? route('eventos.editar-derivacion', $evento->id_evento) : route('eventos.ver', $evento->id_evento) }}'">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $tipoColor }}">
                                {{ $evento->tipo_evento?->label() ?? 'N/A' }}
                            </span>
                        </td>
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
                            <x-boton-eliminar 
                                :route="route('eventos.destroy', $evento->id_evento)"
                                message="¿Está seguro que desea eliminar este evento?"
                            />
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

    <!-- Modal eliminar -->
    <div x-show="mostrarModal"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="cerrar()"
        class="fixed inset-0 z-50 flex items-center justify-center"
        role="dialog">

        <div class="fixed inset-0 bg-black/50"
            @click="cerrar()"></div>

        <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-xl p-6">

            <h3 class="text-lg font-semibold mb-4">
                ¿Confirmar eliminación?
            </h3>

            <p class="text-gray-600 mb-6">
                {{ $message ?? '¿Está seguro que desea eliminar este registro?' }}
            </p>

            <div class="flex justify-end gap-3">
                <button type="button"
                        @click="cerrar()"
                        class="btn-volver">
                    Cancelar
                </button>

                <button type="button"
                        class="btn-eliminar"
                        @click="document.getElementById(routeFormId).submit()">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function eventosData() {
    return {
        mostrarModal: false,
        formId: null,
        message: '',

        abrir(data) {
            this.formId = data.formId
            this.message = data.message
            this.mostrarModal = true
        },

        cerrar() {
            this.mostrarModal = false
            this.formId = null
            this.message = ''
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
                    event.target.checked = !confirmado;
                }
            } catch (error) {
                console.error('Error al actualizar confirmación:', error);
                alert('Error al actualizar la confirmación');
                event.target.checked = !confirmado;
            }
        }
    }
}
</script>
@endsection

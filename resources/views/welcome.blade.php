

@extends('layouts.base')

@section('encabezado', 'Bienvenido ' . $profesional->name)

@section('contenido')
<div class="space-y-8">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- === NOTIFICACIONES === --}}
        <div class="p-4 sm:p-6 bg-white shadow rounded-lg flex flex-col">
            <div class=" text-lg flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    Notificaciones recientes
                    @if ($noLeidas > 0)
                        <span class="ml-2 inline-flex items-center justify-center min-w-[22px] h-[22px] bg-red-500 text-white text-xs font-bold rounded-full px-1">
                            {{ $noLeidas > 99 ? '99+' : $noLeidas }}
                        </span>
                    @endif
                </h2>
                @if ($noLeidas > 0)
                    <button
                        type="button"
                        onclick="fetch('{{ route('notificaciones.leer-todas') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            }
                        }).then(() => window.location.reload())"
                        class="text-xs text-indigo-600 hover:underline focus:outline-none"
                    >
                        Marcar todas como leídas
                    </button>
                @endif
            </div>

            @if ($notificaciones->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-400 gap-2">
                    <i class="fas fa-bell-slash text-3xl"></i>
                    <p class="text-sm">No tenés notificaciones</p>
                </div>
            @else
                <ul class="divide-y divide-gray-100 -mx-4">
                    @foreach ($notificaciones as $notif)
                        <li class="{{ $notif->leida ? 'bg-white' : 'bg-indigo-50' }} hover:bg-gray-50 transition-colors">
                            <form method="POST" action="{{ route('notificaciones.leer', $notif->id_notificacion) }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 flex gap-3 items-start">

                                    {{-- Ícono del tipo --}}
                                    <span class="mt-0.5 shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100">
                                        <i class="fas {{ $notif->tipo->icono() }} {{ $notif->tipo->iconoColor() }} text-sm"></i>
                                    </span>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                                {{ $notif->tipo->etiqueta() }}
                                            </span>
                                            @unless ($notif->leida)
                                                <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                            @endunless
                                        </div>

                                        <p class="text-sm text-gray-700 mt-0.5 leading-snug">{{ $notif->mensaje }}</p>

                                        @if ($notif->recursoBorrado())
                                            <span class="inline-block mt-1 text-xs text-red-500 font-medium">
                                                <i class="fas fa-exclamation-circle mr-0.5"></i> El recurso fue eliminado
                                            </span>
                                        @elseif ($notif->urlDestino())
                                            <span class="inline-block mt-1 text-xs text-indigo-600 font-medium hover:underline">
                                                Ver <i class="fas fa-arrow-right text-[10px]"></i>
                                            </span>
                                        @endif

                                        <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

        </div>

        {{-- CALENDARIO --}}
        <div class="p-4 sm:p-6 bg-white shadow rounded-lg relative"
             x-data="modalEventoData()" x-init="init()">
            
            <h2 class="text-lg sx-text-x1 font-semibold mb-4 text-gray-800">Calendario</h2>

            {{-- Contenedor calendario en el home --}}
            <div id="calendar" class="min-h-[300px] sm:min-h-[350px] lg:min-h-[400px] border rounded-lg overflow-hidden"></div>

            {{-- MODAL DETALLES --}}
            <div x-show="mostrarModal" 
                x-cloak
                x-transition.opacity
                @keydown.escape.window="mostrarModal = false"
                class="fixed inset-0 z-[100] flex items-center justify-center"
                role="dialog"
                aria-modal="true">
                
                <div class="fixed inset-0 bg-black/50"
                    @click="mostrarModal = false"></div>
                
                <div class="relative z-[110] w-full max-w-lg mx-4 sm:mx-auto rounded-2xl bg-white shadow-xl">
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-primary border-b pb-2 mb-4" 
                            x-text="eventoData.title"></h3>
                        
                        <div class="space-y-3 mb-6">
                            <div x-show="eventoData.hora">
                                <span class="font-medium text-gray-700">Hora:</span>
                                <span class="text-gray-600" x-text="eventoData.hora"></span>
                            </div>
                            <div x-show="eventoData.lugar">
                                <span class="font-medium text-gray-700">Lugar:</span>
                                <span class="text-gray-600" x-text="eventoData.lugar"></span>
                            </div>
                            <div x-show="eventoData.creador">
                                <span class="font-medium text-gray-700">Creador:</span>
                                <span class="text-gray-600" x-text="eventoData.creador"></span>
                            </div>
                            <div x-show="eventoData.notas">
                                <span class="font-medium text-gray-700">Notas:</span>
                                <p class="text-gray-600 mt-1" x-text="eventoData.notas"></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 justify-end">
                            <button type="button" @click="mostrarModal = false" class="btn-volver">
                                Cerrar
                            </button>
                            <a :href="eventoData.tipo === 'DERIVACION_EXTERNA' ? `/eventos/${eventoData.id}/editar-derivacion` : `/eventos/${eventoData.id}/editar`" class="btn-aceptar">
                                Editar evento
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL EVENTOS DEL DÍA --}}
            <div x-show="mostrarModalDia" 
                x-cloak
                x-transition.opacity
                @keydown.escape.window="mostrarModalDia = false"
                class="fixed inset-0 z-[100] flex items-center justify-center"
                role="dialog"
                aria-modal="true">
                
                <div class="fixed inset-0 bg-black/50"
                    @click="mostrarModalDia = false"></div>
                
                <div class="relative z-[110] w-full max-w-lg mx-4 sm:mx-auto rounded-2xl bg-white shadow-xl">
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-primary border-b pb-2 mb-4">
                            Eventos del <span x-text="formatearFecha(diaSeleccionado.fecha)"></span>
                        </h3>
                        
                        <div class="mb-6">
                            <template x-if="diaSeleccionado.eventos.length > 0">
                                <div class="space-y-2">
                                    <p class="text-sm text-gray-600 mb-3">Eventos en este día:</p>
                                    <template x-for="evento in diaSeleccionado.eventos" :key="evento.id">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                            <div>
                                                <p class="font-medium text-gray-800" x-text="evento.title"></p>
                                                <p class="text-sm text-gray-500" x-show="evento.hora" x-text="evento.hora"></p>
                                            </div>
                                        <a :href="evento.extendedProps?.tipo === 'DERIVACION_EXTERNA' ? `/eventos/${evento.id}/editar-derivacion` : `/eventos/${evento.id}/editar`" 
                                            class="text-primary hover:text-primary-dark text-sm font-medium">
                                                Editar
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="diaSeleccionado.eventos.length === 0">
                                <p class="text-gray-500 text-center py-4">No hay eventos en este día</p>
                            </template>
                        </div>
                        
                        <div class="flex gap-2 justify-end">
                            <button type="button" @click="mostrarModalDia = false" class="btn-volver">
                                Cerrar
                            </button>
                            <a :href="`/eventos/crear?fecha=${diaSeleccionado.fecha}`" 
                            class="btn-aceptar">
                                Crear evento
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PRÓXIMOS EVENTOS --}}
            <div class="p-4 bg-white shadow rounded-lg">
                <h2 class="text-xl font-semibold mb-3 text-gray-800">Próximos eventos</h2>

                @if ($eventosProximos->isEmpty())
                    <p class="text-gray-500">No tienes eventos próximos.</p>
                @else
                    <ul class="space-y-4">
                        @foreach ($eventosProximos as $evento)
                            <li class="border p-3 rounded hover:bg-gray-100 cursor-pointer"
                                @click="mostrarDetallesEvento({
                                    id: {{ $evento->id_evento }},
                                    title: '{{ $evento->tipo_evento->label() }}',
                                    extendedProps: {
                                        tipo: '{{ $evento->tipo_evento?->value }}',
                                        hora: '{{ $evento->fecha_hora->format('H:i') }}',
                                        lugar: '{{ addslashes($evento->lugar) }}',
                                        creador: '{{ addslashes(optional($evento->profesionalCreador?->persona)->nombre ?? 'Sin asignar') }}',
                                        notas: '{{ addslashes($evento->notas ?? '') }}'
                                    }
                                })">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">                                    <span class="font-semibold text-gray-800">{{ $evento->tipo_evento->label() }}</span>
                                    <span class="text-sm text-gray-600">
                                        {{ $evento->fecha_hora->format('d/m H:i') }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    Lugar: {{ $evento->lugar }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div> 

    </div>

</div>

{{-- Script Alpine de manejo modal --}}
<script>
function modalEventoData() {
    return {
        mostrarModal: false,
        mostrarModalDia: false,
        eventoData: {
            id: null,
            title: '',
            tipo: '',
            hora: '',
            lugar: '',
            creador: '',
            notas: ''
        },
        diaSeleccionado: {
            fecha: '',
            eventos: []
        },
        mostrarDetallesEvento(evento) {
            // Permitir compatibilidad con estructura de eventos del calendario y próximos eventos
            if (evento.extendedProps) {
                this.eventoData = {
                    id: evento.id,
                    title: evento.title,
                    tipo: evento.extendedProps.tipo || '',
                    hora: evento.extendedProps.hora || '',
                    lugar: evento.extendedProps.lugar || '',
                    creador: evento.extendedProps.creador || '',
                    notas: evento.extendedProps.notas || ''
                };
            } else {
                this.eventoData = evento;
            }
            this.mostrarModal = true;
        },
        init() {
            window.addEventListener('mostrar-detalle-evento', (e) => {
                this.eventoData = e.detail;
                this.mostrarModal = true;
            });
            window.addEventListener('mostrar-eventos-dia', (e) => {
                this.diaSeleccionado = e.detail;
                this.mostrarModalDia = true;
            });
        },
        formatearFecha(fecha) {
            if (!fecha) return '';
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const partes = fecha.split('-');
            const dia = parseInt(partes[2]);
            const mes = meses[parseInt(partes[1]) - 1];
            const anio = partes[0];
            return `${dia} de ${mes} de ${anio}`;
        },
    };
}
</script>


@push('scripts')
    @vite(['resources/js/calendario.js'])
@endpush

@endsection

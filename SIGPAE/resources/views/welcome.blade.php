@extends('layouts.base')

@section('encabezado', '')  <!--Encabezado de la pagina de bienvenida, si se quiere se pone algo, si no, no-->

@section('contenido')
    <div class="h-full flex items-end justify-end pb-6" x-data="modalEventoData()" x-init="init()">
        <!-- Contenedor del calendario en esquina inferior derecha -->
        <div class="fixed bottom-6 right-6 w-96">
            <div id="calendar" class="bg-white rounded-lg shadow-md p-3 text-sm"></div>
        </div>

        <!-- Modal de detalles del evento -->
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
                    <h3 class="text-lg font-semibold text-primary border-b pb-2 mb-4" x-text="eventoData.title"></h3>
                    
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
                        <a :href="`/eventos/${eventoData.id}/editar`" class="btn-aceptar">
                            Editar evento
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de eventos del día -->
        <div x-show="mostrarModalDia" 
             x-cloak
             x-transition.opacity
             @keydown.escape.window="mostrarModalDia = false"
             class="fixed inset-0 z-[100] flex items-center justify-center"
             role="dialog"
             aria-modal="true">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50"
                 @click="mostrarModalDia = false"></div>
            
            <!-- Panel -->
            <div class="relative z-[110] w-full max-w-lg rounded-2xl bg-white shadow-xl">
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
                                        <a :href="`/eventos/${evento.id}/editar`" 
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
                        <a :href="`/eventos/crear?fecha=${diaSeleccionado.fecha}`" class="btn-aceptar">
                            Crear evento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function modalEventoData() {
        return {
            mostrarModal: false,
            mostrarModalDia: false,
            eventoData: {
                id: null,
                title: '',
                hora: '',
                lugar: '',
                creador: '',
                notas: ''
            },
            diaSeleccionado: {
                fecha: '',
                eventos: []
            },
            
            init() {
                // Escuchar evento personalizado del calendario para detalle de evento
                window.addEventListener('mostrar-detalle-evento', (e) => {
                    this.eventoData = e.detail;
                    this.mostrarModal = true;
                });
                
                // Escuchar evento personalizado para eventos del día
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
            
            mostrarDetallesEvento(evento) {
                this.eventoData = {
                    id: evento.id,
                    title: evento.title,
                    hora: evento.extendedProps?.hora || '',
                    lugar: evento.extendedProps?.lugar || '',
                    creador: evento.extendedProps?.creador || '',
                    notas: evento.extendedProps?.notas || ''
                };
                this.mostrarModal = true;
            }
        };
    }
    </script>

    @push('scripts')
        @vite(['resources/js/calendario.js'])
    @endpush
@endsection
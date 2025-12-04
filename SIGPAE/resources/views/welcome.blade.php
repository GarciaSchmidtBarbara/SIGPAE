@extends('layouts.base')

<<<<<<< HEAD
@section('encabezado', 'Bienvenido ' . auth()->user()->name)

@section('contenido')
<div class="grid grid-cols-5 gap-6">

    {{--Notificaciones / Eventos del día --}}
    <div class="col-span-3 space-y-6">

        <h2 class="text-2xl font-semibold text-gray-800">Eventos del día</h2>

        <div class="grid grid-cols-3 gap-4">
            {{-- ejemplos --}}
            <div class="p-4 bg-white shadow rounded-lg">
                <p class="font-semibold">Notificación 1</p>
                <p class="text-sm text-gray-600">Detalle del evento...</p>
            </div>

            <div class="p-4 bg-white shadow rounded-lg">
                <p class="font-semibold">Notificación 2</p>
                <p class="text-sm text-gray-600">Detalle del evento...</p>
            </div>

            <div class="p-4 bg-white shadow rounded-lg">
                <p class="font-semibold">Notificación 3</p>
                <p class="text-sm text-gray-600">Detalle del evento...</p>
            </div>
        </div>

    </div>

    {{-- Calendario + Próximos eventos --}}
    <div class="col-span-2 space-y-6">

        {{-- CALENDARIO --}}
        <div class="p-4 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Calendario</h2>

            {{-- Google Calendar / FullCalendar --}}
            <div class="h-64 flex items-center justify-center border rounded-lg text-gray-500">
                Calendario aquí
            </div>
        </div>

        @if (!auth()->user()->google_refresh_token)
            <div class="p-4 bg-red-100 border border-red-300 shadow rounded-lg text-center">
                <h3 class="text-lg font-semibold text-red-800 mb-2">
                    ⚠️ Sincronización Necesaria
                </h3>
                <p class="text-sm text-red-700 mb-4">
                    Conecta tu cuenta de Google para habilitar la sincronización de eventos y el envío de correos.
                </p>
                <a href="{{ route('google.login') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.81-4.75h1.76v-1.16H7.19a4.26 4.26 0 0 1 0-3.32h4.51V7.19H7.19c.12-.46.33-.87.62-1.23l1.11-1.11L8.38 4.75l-1.11 1.11c-.3.37-.51.78-.63 1.23h1.76v1.16H7.19c-.12.46-.33.87-.62 1.23l-1.11 1.11.78.78 1.11-1.11c.3-.37.51-.78.63-1.23h1.76v1.16H7.19a4.26 4.26 0 0 1 0 3.32z"/></svg>
                    Conectar con Google Calendar
                </a>
            </div>
        @else
            <div class="p-4 bg-green-100 border border-green-300 shadow rounded-lg text-center">
                <h3 class="text-lg font-semibold text-green-800 mb-2">
                    ✅ Conexión Activa
                </h3>
                <p class="text-sm text-green-700">
                    Tu cuenta está sincronizada.
                </p>
            </div>
        @endif

        {{-- PRÓXIMOS EVENTOS --}}
        <div class="p-4 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-3 text-gray-800">Próximos eventos</h2>
        </div>

    </div>

</div>
@endsection
{{-- Momentaneo
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
--}}
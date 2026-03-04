@extends('layouts.base')

@section('encabezado', 'Panel de Reportes')

@section('contenido')

    <div class="container mx-auto p-2 md:p-6">
        
        {{-- Título de la sección --}}
        <h2 class="text-lg md:text-xl font-bold text-gray-700 mb-6 uppercase tracking-wide px-2">
            Resumen General
        </h2>

        {{-- GRILLA DE TARJETAS (RESPONSIVA) --}}
     
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            {{-- ALUMNOS --}}
       <div class="relative bg-[#b799ff] rounded-2xl p-5 shadow-lg text-white overflow-hidden group hover:scale-105 transition-all duration-300 min-h-[140px]">
            <div class="flex justify-between items-start z-10 relative h-full">
                
                {{-- Lado Izquierdo: Números y Filtros --}}
                <div class="flex flex-col">
                    <span class="block text-4xl md:text-5xl font-extrabold leading-none">{{ $totalAlumnos }}</span>
                    <span class="text-xs font-bold uppercase tracking-wider opacity-90 mt-2 block">Alumnos</span>
                    
                 
                </div>

                {{-- Lado Derecho: El Icono en su burbuja --}}
                <div class="bg-white/20 w-12 h-12 md:w-14 md:h-14 rounded-full flex items-center justify-center shrink-0">
                    <i class="fas fa-user-graduate text-2xl md:text-3xl"></i>
                </div>
            </div>

            {{-- Efecto de burbuja decorativa de fondo --}}
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white opacity-10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
        </div>

            {{-- INTERVENCIONES --}}
            <div class="relative bg-[#b8f2e6] rounded-2xl p-5 shadow-lg text-white overflow-hidden group hover:scale-105 transition-all duration-300">
                <div class="flex justify-between items-center z-10 relative">
                    <div>
                        <span class="block text-4xl md:text-5xl font-extrabold">{{ $totalIntervenciones }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-90 mt-1 block">Intervenciones</span>

                           {{-- Filtros rápidos sutiles --}}
                    <div class="mt-4 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <a href="?meses=1" class="text-[9px] bg-white/20 px-2 py-1 rounded hover:bg-white/40">1M</a>
                        <a href="?meses=3" class="text-[9px] bg-white/20 px-2 py-1 rounded hover:bg-white/40">3M</a>
                        <a href="?meses=6" class="text-[9px] bg-white/20 px-2 py-1 rounded hover:bg-white/40">6M</a>
                        <a href="?meses=12" class="text-[9px] bg-white/20 px-2 py-1 rounded hover:bg-white/40">1A</a>
                    </div>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-file-signature text-3xl"></i>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white opacity-10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            </div>

            {{-- PLAN DE ACCIÓN --}}
            <div class="relative bg-[#9ad5ff] rounded-2xl p-5 shadow-lg text-white overflow-hidden group hover:scale-105 transition-all duration-300">
                <div class="flex justify-between items-center z-10 relative">
                    <div>
                        <span class="block text-4xl md:text-5xl font-extrabold">{{ $totalPlanesDeAccion }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-90 mt-1 block">Planes</span>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-clipboard-list text-3xl"></i>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white opacity-10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            </div>

            {{-- USUARIOS ACTIVOS --}}
            <div class="relative bg-[#ffb3ba] rounded-2xl p-5 shadow-lg text-white overflow-hidden group hover:scale-105 transition-all duration-300">
                <div class="flex justify-between items-center z-10 relative">
                    <div>
                        <span class="block text-4xl md:text-5xl font-extrabold">{{ $usuariosActivos ?? 0 }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-90 mt-1 block">U. Activos</span>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-user-check text-3xl"></i>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white opacity-10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            </div>

            {{-- EVENTOS DEL MES --}}
            <div class="relative bg-[#ffe4a1] rounded-2xl p-5 shadow-lg text-white overflow-hidden group hover:scale-105 transition-all duration-300">
                <div class="flex justify-between items-center z-10 relative">
                    <div>
                        <span class="block text-4xl md:text-5xl font-extrabold">{{ $eventosDelMes ?? 0 }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-90 mt-1 block">Eventos Mes</span>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-3xl"></i>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white opacity-10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            </div>

        </div>

        {{-- Selector de Filtro --}}
       {{-- OTRO TIPO DE FILTRO MÁS CLÁSICO (COMENTADO PORQUE YA HAY FILTROS RÁPIDOS EN LA TARJETA DE INTERVENCIONES) --}}
         {{-- 
       <div class="flex justify-end mb-4 px-2">
            <form action="{{ route('reportes.principal') }}" method="GET" id="filtroForm">
                <select name="meses" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="3" {{ $mesesFiltro == 3 ? 'selected' : '' }}>Últimos 3 meses</option>
                    <option value="6" {{ $mesesFiltro == 6 ? 'selected' : '' }}>Últimos 6 meses</option>
                    <option value="12" {{ $mesesFiltro == 12 ? 'selected' : '' }}>Último año</option>
                </select>
            </form>
        </div>
        --}}
        {{-- SECCIÓN DE GRÁFICOS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-10">
            
            {{-- Gráfico Evolución --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-700 uppercase tracking-tight">Actividad de Intervenciones</h3>
                </div>
                <div class="h-72">
                    <canvas id="chartEvolucion"></canvas>
                </div>
            </div>

            {{-- Gráfico de Torta --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-700 uppercase tracking-tight mb-6">Estado de Planes</h3>
                <div class="h-72">
                    <canvas id="chartPlanes"></canvas>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
{{-- Cargamos Chart.js primero --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Usamos un evento para asegurarnos que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Configuración para el gráfico de líneas (Evolución)
        const ctxEvolucion = document.getElementById('chartEvolucion');
        if(ctxEvolucion) {
            new Chart(ctxEvolucion.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($evolucionIntervenciones->pluck('mes')->toArray()), 
                    datasets: [{
                        label: 'Intervenciones',
                        data: @json($evolucionIntervenciones->pluck('total')->toArray()), 
                        borderColor: '#b799ff',
                        backgroundColor: 'rgba(183, 153, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Configuración para el gráfico de torta (Planes)
        const ctxPlanes = document.getElementById('chartPlanes');
        if(ctxPlanes) {
            new Chart(ctxPlanes.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($estadosPlanes->pluck('label')->toArray()),           
                    datasets: [{
                        data: @json($estadosPlanes->pluck('total')->toArray()), 
                        backgroundColor: ['#9ad5ff', '#ffb3ba', '#b8f2e6', '#ffe4a1'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
</script>
@endpush
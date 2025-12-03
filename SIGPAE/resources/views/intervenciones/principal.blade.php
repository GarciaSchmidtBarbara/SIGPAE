@extends('layouts.base')

@section('encabezado', 'Todas las Intervenciones')

@section('contenido')

    {{-- Mensajes de estado --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

<div class="p-6">
    <form id="form-intervencion" method="GET" action="{{ route('intervenciones.principal') }}" class="flex gap-2 mb-6 flex-nowrap items-center">    
        <a class="btn-aceptar" href="{{ route('intervenciones.crear') }}">Crear Intervención</a>
        
        <select name="tipo_intervencion" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los tipos</option>
            @foreach($tiposIntervencion as $tipo)
                <option value="{{ $tipo }}" {{ request('tipo_intervencion') === $tipo ? 'selected' : '' }}>
                    {{ $tipo }}
                </option>
            @endforeach
        </select>

        <input name="nombre" value="{{ request('nombre') }}" placeholder="Nombre/DNI" class="border px-2 py-1 rounded w-1/5">

        <select name="aula" class="border px-2 py-1 rounded w-1/5">
            <option value="">Todos los cursos</option>
            @foreach($aulas as $curso)
                <option value="{{ $curso->id }}" {{ request('aula') == $curso->id ? 'selected' : '' }}>
                    {{ $curso->descripcion }}
                </option>
            @endforeach
        </select>

        <p>Desde</p>
        <input type="date" name="fecha_desde" class="border px-2 py-1 rounded" value="{{ request('fecha_desde') }}">
        <p>Hasta</p>
        <input type="date" name="fecha_hasta" class="border px-2 py-1 rounded" value="{{ request('fecha_hasta') }}">

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('intervenciones.principal') }}" >Limpiar</a>
        <button type="button" id="btn-imprimir" class="btn-aceptar">Imprimir</button>
    </form>

    <x-tabla-dinamica 
        :columnas="[
            ['key' => 'fecha_hora_intervencion', 'label' => 'Fecha y Hora'],
            ['key' => 'tipo_intervencion', 'label' => 'Tipo'],
            ['key' => 'alumnos', 'label' => 'Destinatarios'],
            ['key' => 'profesionales', 'label' => 'Intervinientes'],
        ]"
        :filas="$intervenciones"
        :acciones="fn($fila) => view('components.boton-eliminar', [
            'route' => route('intervenciones.eliminar', $fila['id_intervencion']),
            'texto' => 'Eliminar'
        ])->render()"
        idCampo="id_intervencion"
        class="tabla-imprimir"
        :filaEnlace="fn($fila) => route('intervenciones.editar', data_get($fila, 'id_intervencion'))"
    >
        <x-slot:accionesPorFila>
            @once
                @php
                    $accionesPorFila = function ($fila) {
                        $activo = data_get($fila, 'activo');
                        $ruta = route('intervenciones.cambiarActivo', data_get($fila, 'id_intervencion'));
                        return view('components.boton-estado', [
                            'activo' => $activo,
                            'route' => $ruta
                        ])->render();
                    };
                @endphp
            @endonce
        </x-slot:accionesPorFila>

    </x-tabla-dinamica>

    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}" >Volver</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('btn-imprimir').addEventListener('click', function() {
        // 1. Selecciona el elemento de la tabla que deseas imprimir
        const contenidoTabla = document.querySelector('.tabla-imprimir') || document.querySelector('table');

        if (contenidoTabla) {
            // 2. Crea una nueva ventana
            const ventanaImpresion = window.open('', '', 'height=600,width=800');
            
            // 3. Escribe el contenido HTML en la nueva ventana
            ventanaImpresion.document.write('<html><head><title>Intervenciones</title>');
            
            // Copia los estilos de la página actual para que la tabla se vea bien.
            ventanaImpresion.document.write('<style>');
            
            // Estilos básicos para la impresión
            ventanaImpresion.document.write(`
                body { font-family: sans-serif; margin: 20px; }
                h1 { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }

                /* Oculta las columnas de "Acciones" en la impresión*/
                table thead th:last-child {display: none !important;}
                .accion-col {display: none !important;}
            `);
            
            ventanaImpresion.document.write('</style>');
            ventanaImpresion.document.write('</head><body>');
            ventanaImpresion.document.write('<h2>Historial Intervenciones</h2>');
            
            // Agrega el HTML de la tabla, incluyendo el encabezado
            ventanaImpresion.document.write(contenidoTabla.outerHTML); 
            
            ventanaImpresion.document.write('</body></html>');
            ventanaImpresion.document.close();
            
            // 4. Llama a la función de impresión del navegador
            ventanaImpresion.print();
            
            // 5. Cierra la ventana después de la impresión (opcional)
            setTimeout(() => { ventanaImpresion.close(); }, 10);

        } else {
            alert('No se encontró la tabla para imprimir.');
        }
    });
</script>
@endpush

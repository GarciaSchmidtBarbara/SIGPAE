@extends('layouts.base')

@section('encabezado', 'Todos los documentos')

@section('contenido')

{{-- ── Mensajes de éxito / error ─────────────────────────────────── --}}
@if(session('success') === 'documento_eliminado')
    <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 3000)"
         x-transition class="fixed bottom-6 right-6 z-50">
        <x-ui.modal-alert variant="success" message="Documento eliminado" />
    </div>
@endif

@if(session('success') === 'documento_cargado')
    <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 3000)"
         x-transition class="fixed bottom-6 right-6 z-50">
        <x-ui.modal-alert variant="success" message="Documento cargado con éxito" />
    </div>
@endif

@if(session('error_descarga'))
    <div x-data="{ open: true }">
        <x-ui.modal-error message="No se pudo descargar el archivo. Intente nuevamente o contacte al administrador." />
    </div>
@endif

@if(session('error_no_visualizable'))
    <div x-data="{ open: true }">
        <x-ui.modal-error message="El documento no puede visualizarse en línea. Descárguelo para consultarlo." />
    </div>
@endif

<div class="p-6">

    {{-- ── Barra de filtros ───────────────────────────────────────── --}}
    <form method="GET" action="{{ route('documentos.principal') }}"
          class="flex flex-wrap gap-2 mb-6 items-center">

        <a class="btn-aceptar" href="{{ route('documentos.crear') }}">Subir Documento</a>

        {{-- Contexto --}}
        <select name="contexto" class="border px-2 py-1 rounded text-sm">
            <option value="">Contexto</option>
            <option value="perfil_alumno" {{ request('contexto') === 'perfil_alumno' ? 'selected' : '' }}>Perfil de alumno</option>
            <option value="plan_accion"   {{ request('contexto') === 'plan_accion'   ? 'selected' : '' }}>Plan de acción</option>
            <option value="intervencion"  {{ request('contexto') === 'intervencion'  ? 'selected' : '' }}>Intervención</option>
            <option value="institucional" {{ request('contexto') === 'institucional' ? 'selected' : '' }}>Institucional</option>
        </select>

        {{-- Nombre --}}
        <input type="text"
               name="nombre"
               value="{{ request('nombre') }}"
               placeholder="Nombre documento"
               class="border px-2 py-1 rounded text-sm w-52">

        <button type="submit" class="btn-aceptar">Filtrar</button>
        <a class="btn-aceptar" href="{{ route('documentos.principal') }}">Limpiar</a>
    </form>

    {{-- ── Tabla ───────────────────────────────────────────────────── --}}
    @if($documentos->isEmpty())
        {{-- Sin resultados --}}
        <div x-data="{ open: true }">
            <x-ui.modal-alert variant="info"
                              message="No se encontraron documentos con los criterios ingresados" />
        </div>
    @else
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200 bg-white text-sm">
                <thead class="bg-indigo-50 text-indigo-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Contexto</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left">Pertenece a</th>
                        <th class="px-4 py-3 text-left">Formato</th>
                        <th class="px-4 py-3 text-left">Tamaño</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($documentos as $doc)
                        <tr class="hover:bg-indigo-50/40 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $doc['fecha'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($doc['contexto']) {
                                        'perfil_alumno' => 'bg-blue-100 text-blue-700',
                                        'plan_accion'   => 'bg-amber-100 text-amber-700',
                                        'intervencion'  => 'bg-purple-100 text-purple-700',
                                        default         => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $doc['etiqueta_contexto'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $doc['nombre'] }}</td>
                            <td class="px-4 py-3">{{ $doc['pertenece_a'] }}</td>
                            <td class="px-4 py-3 uppercase text-gray-500">{{ $doc['tipo_formato'] }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $doc['tamanio_formateado'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3">

                                    {{-- Ver online (solo si es PDF/JPG/PNG) --}}
                                    @if($doc['visualizable_online'] && !$doc['disponible_presencial'])
                                        <a href="{{ route('documentos.ver', $doc['id_documento']) }}"
                                           target="_blank"
                                           title="Ver en línea"
                                           class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif

                                    {{-- Descargar --}}
                                    <a href="{{ route('documentos.descargar', $doc['id_documento']) }}"
                                       title="Descargar"
                                       class="text-gray-500 hover:text-indigo-700">
                                        <i class="fas fa-download"></i>
                                    </a>

                                    {{-- Eliminar --}}
                                    <button
                                        type="button"
                                        title="Eliminar"
                                        class="text-red-500 hover:text-red-700"
                                        @click="$dispatch('abrir-modal-confirmar', {
                                            formId: 'form-eliminar-{{ $doc['id_documento'] }}',
                                            message: '¿Desea eliminar el documento?'
                                        })">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <form id="form-eliminar-{{ $doc['id_documento'] }}"
                                          action="{{ route('documentos.eliminar', $doc['id_documento']) }}"
                                          method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Botones inferiores --}}
    <div class="fila-botones mt-8">
        <a class="btn-volver" href="{{ url()->previous() }}">Volver</a>
    </div>
</div>

@endsection

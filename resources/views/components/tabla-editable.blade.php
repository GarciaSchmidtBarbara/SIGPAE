@props([
    'listado' => [], // lista existente (si es edición)
    'titulo' => null,
])

{{-- Campo oculto para inicializar Alpine sin problemas de escape --}}
<input type="hidden" x-ref="listadoJson" value='@json($listado)'>

<div 
    x-data="{
        filas: [],
        nuevaFila() { return { nombre: '', apellido: '', descripcion: '' }; },
        agregarFila() { this.filas.push(this.nuevaFila()); },
        eliminarFila(index) { this.filas.splice(index, 1); }
    }" 
    x-init="filas = JSON.parse($refs.listadoJson.value)"
    class="mt-8"
>

    {{-- Título dinámico --}}
    <h3 class="font-medium text-base text-gray-700 mb-2">{{ $titulo }}</h3>

    {{-- 1. Vista Móvil: Cada fila es una tarjeta--}}
    <div class="md:hidden space-y-4">
        <template x-for="(fila, index) in filas" :key="index">
            <div class="border rounded-lg p-4 shadow-sm bg-white space-y-3">

                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Nombre</label>
                    <input 
                        type="text"
                        x-model="fila.nombre"
                        class="w-full mt-1 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500"
                        placeholder="Nombre"
                        required
                    >
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Apellido</label>
                    <input 
                        type="text"
                        x-model="fila.apellido"
                        class="w-full mt-1 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500"
                        placeholder="Apellido"
                        required
                    >
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Descripción</label>
                    <input 
                        type="text"
                        x-model="fila.descripcion"
                        class="w-full mt-1 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500"
                        placeholder="Descripción del asistente"
                        required
                    >
                </div>

                <div class="flex justify-end pt-2 border-t">
                    <button 
                        type="button" 
                        @click="eliminarFila(index)"
                        class="text-red-500 hover:text-red-700 text-sm"
                    >
                        Eliminar
                    </button>
                </div>

            </div>
        </template>
    </div>

    {{-- 2. Vista Escritorio: Tabla tradicional --}}
    {{-- Tabla de asistentes --}}
    <div class="hidden md:block overflow-x-auto rounded-t-l">
        <table  class="modern-table text-sm">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Descripción</th>
                    <th class="no-imprimir">Opciones</th>
                </tr>
            </thead>

            <tbody>
                <template x-for="(fila, index) in filas" :key="index">
                    <tr>
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.nombre"
                                class="w-full h-full px-2 py-1 text-sm outline-none focus:bg-blue-50"
                                placeholder="Nombre" required>
                        </td>
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.apellido"
                                class="w-full h-full px-2 py-1 text-sm outline-none focus:bg-blue-50"
                                placeholder="Apellido" required>
                        </td>
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.descripcion"
                                class="w-full h-full px-2 py-1 text-sm outline-none focus:bg-blue-50"
                                placeholder="Descripción del asistente" required>
                        </td>
                        <td class="border border-gray-400 px-2 text-center">
                            <button type="button" @click="eliminarFila(index)"
                                class="text-red-500 hover:text-red-700" title="Eliminar fila">✕</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Botón Agregar --}}
    <div class="mt-3 flex justify-center">
        <button type="button" @click="agregarFila()"
            class="group flex items-center justify-center 
                w-10 h-10 md:w-8 md:h-8 
                rounded-full border-2 border-gray-400 
                hover:border-blue-500 hover:bg-blue-50 transition">
            +
        </button>
    </div>

    {{-- Campo hidden que lleva todo al controller --}}
    <input type="hidden" name="otros_asistentes_json" :value="JSON.stringify(filas)">
</div>

@props([
    'listado' => [] // lista existente (si es edición)
])

<div x-data="{
        filas: (() => {
            try {
                const data = @json($listado);
                return Array.isArray(data) && data.length > 0
                    ? data
                    : [{ nombre: '', apellido: '', descripcion: '' }];
            } catch (e) {
                return [{ nombre: '', apellido: '', descripcion: '' }];
            }
        })(),
        nuevaFila() { return { nombre: '', apellido: '', descripcion: '' }; },
        agregarFila() { this.filas.push(this.nuevaFila()); },
        eliminarFila(index) { this.filas.splice(index, 1); }
    }" class="mt-8">

    <h3 class="font-bold text-lg mb-2 text-gray-800">Otros Asistentes:</h3>

    <div class="overflow-x-auto rounded-t-lg">
        <table class="w-full border-collapse border border-gray-400">
            <thead>
                <tr class="bg-gray-100 text-sm uppercase text-gray-600">
                    <th class="border border-gray-400 px-4 py-2 text-left">Nombre</th>
                    <th class="border border-gray-400 px-4 py-2 text-left">Apellido</th>
                    <th class="border border-gray-400 px-4 py-2 text-left">Descripción</th>
                    <th class="border border-gray-400 px-4 py-2 text-center no-imprimir">Opciones</th>
                </tr>
            </thead>

            <tbody>
                <template x-for="(fila, index) in filas" :key="index">
                    <tr>
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.nombre"
                                class="w-full h-full px-2 py-1 outline-none focus:bg-blue-50 text-gray-700"
                                placeholder="Nombre" required>
                        </td>
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.apellido"
                                class="w-full h-full px-2 py-1 outline-none focus:bg-blue-50 text-gray-700"
                                placeholder="Apellido" required>
                        </td>
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.descripcion"
                                class="w-full h-full px-2 py-1 outline-none focus:bg-blue-50 text-gray-700"
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
            class="group flex items-center justify-center w-8 h-8 rounded-full border-2 border-gray-400 hover:border-blue-500 hover:bg-blue-50 transition">
            +
        </button>
    </div>

    {{-- Campo hidden que lleva todo al controller --}}
    <input type="hidden" name="otros_asistentes_json" :value="JSON.stringify(filas)">
</div>

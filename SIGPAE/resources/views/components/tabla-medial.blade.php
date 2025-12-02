<div x-data="{ 
    filas: [{ nombre: '', grado: '', motivo: '', descripcion: '', modalidad: '', profesionales: '' }] 
}" class="mt-8">

    {{-- TABLA --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-400 text-sm">
            <thead>
                <tr class="bg-gray-100 uppercase text-gray-700 font-bold text-center">
                    <th class="border border-gray-400 p-2 w-8">N°</th>
                    <th class="border border-gray-400 p-2 w-1/6">Nombre y Apellido</th>
                    <th class="border border-gray-400 p-2 w-16">Grado</th>
                    <th class="border border-gray-400 p-2 w-1/6">Motivo de Intervención</th>
                    <th class="border border-gray-400 p-2 w-1/4">Breve Descripción del Proceso</th>
                    <th class="border border-gray-400 p-2 w-1/6">Modalidad de Intervención</th>
                    <th class="border border-gray-400 p-2 w-1/6">Profesionales Intervinientes</th>
                    <th class="border border-gray-400 p-2 w-8 no-imprimir">❌</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(fila, index) in filas" :key="index">
                    <tr>
                        {{-- NÚMERO (Automático) --}}
                        <td class="border border-gray-400 p-1 text-center" x-text="index + 1"></td>
                        
                        {{-- NOMBRE --}}
                        <td class="border border-gray-400 p-0">
                            <textarea x-model="fila.nombre" rows="2" class="w-full h-full p-1 outline-none resize-none bg-transparent"></textarea>
                        </td>

                        {{-- GRADO --}}
                        <td class="border border-gray-400 p-0">
                            <input type="text" x-model="fila.grado" class="w-full h-full p-1 text-center outline-none bg-transparent">
                        </td>

                        {{-- MOTIVO --}}
                        <td class="border border-gray-400 p-0">
                            <textarea x-model="fila.motivo" rows="4" class="w-full h-full p-1 outline-none resize-none bg-transparent"></textarea>
                        </td>

                        {{-- DESCRIPCIÓN --}}
                        <td class="border border-gray-400 p-0">
                            <textarea x-model="fila.descripcion" rows="4" class="w-full h-full p-1 outline-none resize-none bg-transparent"></textarea>
                        </td>

                        {{-- MODALIDAD --}}
                        <td class="border border-gray-400 p-0">
                            <textarea x-model="fila.modalidad" rows="2" class="w-full h-full p-1 outline-none resize-none bg-transparent"></textarea>
                        </td>

                        {{-- PROFESIONALES --}}
                        <td class="border border-gray-400 p-0">
                            <textarea x-model="fila.profesionales" rows="2" class="w-full h-full p-1 outline-none resize-none bg-transparent"></textarea>
                        </td>

                        {{-- BOTÓN BORRAR (Solo pantalla) --}}
                        <td class="border border-gray-400 p-0 text-center no-imprimir">
                            <button type="button" @click="filas.splice(index, 1)" class="text-red-500 font-bold hover:scale-125 transition">
                                &times;
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- tengo opcion de agregar fila --}}
    <div class="mt-3 flex justify-center no-imprimir">
        <button 
            type="button" 
            @click="filas.push({ nombre: '', grado: '', motivo: '', descripcion: '', modalidad: '', profesionales: '' })"
            class="group flex items-center justify-center w-8 h-8 rounded-full border-2 border-gray-400 hover:border-blue-500 hover:bg-blue-50 transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500 group-hover:text-blue-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </div>

    {{-- INPUT OCULTO PARA ENVIAR DATOS AL SERVIDOR --}}
    <input type="hidden" name="medial_json" :value="JSON.stringify(filas)">
</div>
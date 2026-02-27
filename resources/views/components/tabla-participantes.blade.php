@props(['listado'])


<div x-data='{ 
    filas: @json($listado) 
}' class="mt-8">

    <h3 class="font-bold text-lg mb-2 text-gray-800">Participantes:</h3>
    
    {{-- 1. Vista Móvil: Cada fila es una tarjeta --}}
    <div class="md:hidden space-y-4">
        <template x-for="(persona, index) in filas" :key="index">
            <div class="border rounded-lg p-4 shadow-sm bg-white space-y-3">

                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Función</label>
                    <input 
                        type="text"
                        x-model="persona.cargo"
                        class="w-full mt-1 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Nombre</label>
                    <input 
                        type="text"
                        x-model="persona.nombre"
                        class="w-full mt-1 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="flex items-center justify-between pt-2 border-t">

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input 
                            type="checkbox"
                            x-model="persona.asistio"
                            class="w-4 h-4 accent-blue-600"
                        >
                        Asistió
                    </label>

                    <button 
                        type="button" 
                        @click="filas.splice(index, 1)" 
                        class="text-red-500 hover:text-red-700"
                    >
                        ✕
                    </button>

                </div>

            </div>
        </template>
    </div>

    {{-- 2. Vista Escritorio: Tabla tradicional --}}
    <div class="hidden md:block overflow-x-auto rounded-t-lg">
        <table  class="modern-table text-sm">
           <thead>
                <tr class="bg-gray-100 text-sm uppercase text-gray-600">
               
                    <th class="border border-gray-400 px-4 py-2 text-left w-1/5">Función</th>
                    
                  
                    <th class="border border-gray-400 px-4 py-2 text-left w-3/5">Nombre</th>
                    
                 
                    <th class="border border-gray-400 px-4 py-2 text-center w-1/5 no-imprimir">Opciones</th>
                </tr>
            </thead>
            <tbody>
                
                <template x-for="(persona, index) in filas" :key="index">
                    <tr>
                        <td class="border border-gray-400 p-0">
                            {{--se actualiza la variable --}}
                            <input 
                                type="text" 
                                x-model="persona.cargo"
                                class="w-full h-full px-2 py-1 text-sm outline-none focus:bg-blue-50 text-gray-700"
                            >
                        </td>
                        <td class="border border-gray-400 p-0">
                            <input 
                                type="text"                                 
                                x-model="persona.nombre"
                                class="w-full h-full px-2 py-1 text-sm outline-none focus:bg-blue-50 text-gray-700"
                            >
                        </td>
                        <td class="border border-gray-400 px-2 py-1 text-center flex items-center justify-center gap-2">
                            
                            {{-- Checkbox de Asistencia --}}
                            <input 
                                type="checkbox"                                 
                                x-model="persona.asistio"
                                class="w-4 h-4 accent-blue-600"
                            >

                            {{-- Botón rojo para eliminar fila (Opcional pero útil) --}}
                            <button type="button" @click="filas.splice(index, 1)" class="text-red-500 hover:text-red-700" title="Quitar fila">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                        </td>
                    </tr>
                </template>

            </tbody>
        </table>
    </div>
    
    {{-- 3. Botón Agregar (+): Ahora tiene superpoderes --}}
    <div class="mt-3 flex justify-center">
        <button 
            type="button" 
            {{-- MAGIA: Al hacer click, empujamos (.push) un objeto vacío al array 'filas' --}}
            @click="filas.push({ cargo: '', nombre: '', asistio: false })"
            class="group flex items-center justify-center w-8 h-8 rounded-full border-2 border-gray-400 hover:border-blue-500 hover:bg-blue-50 transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500 group-hover:text-blue-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </div>
  <input type="hidden" name="participantes_json" :value="JSON.stringify(filas)">
  
</div>
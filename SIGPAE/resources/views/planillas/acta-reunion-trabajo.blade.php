@extends('layouts.base')

{{-- CAMBIO 1: El Título correcto --}}
@section('encabezado', 'Acta de Reunión Equipo Interdisciplinario')

@section('contenido')

    <form action="{{ route('planillas.acta-reunion-trabajo.store') }}" method="POST">
   
    @csrf
    <div class="max-w-4xl mx-auto mt-6 px-4">
        <div class="bg-white p-8 rounded-lg shadow-lg border border-gray-200">

            {{-- no es necesario el grado? PREGUNTAR --}}
            <div class="flex flex-wrap items-center justify-center gap-6 mb-8">
               
                <div class="flex items-center gap-2">
                    <label for="fecha" class="font-bold text-gray-700">Fecha:</label>
                    <input 
                     type="date"
                     id="fecha"
                     name="fecha"
                     class="border border-gray-300 rounded px-2 py-1 text-gray-600">
                </div>
                <div class="flex items-center gap-2">
                    <label for="hora" class="font-bold text-gray-700">Hora:</label>
                    <input 
                      type="time" 
                      id="hora" 
                      name="hora" class="border border-gray-300 rounded px-2 py-1 text-gray-600">
                </div>
            </div>

              <x-tabla-participantes :listado="$personal" />

            <div class="mt-8 space-y-6">    

             {{-- 1. TEMARIO --}}
             <div x-data="{ contenido: '' }">
                 <label for="temario" class="block font-bold text-gray-700 mb-2">Temario:</label> 
                 <textarea 
                       x-model="contenido"
                       id="temario" 
                       name="temario" 
                       rows="4" 
                       class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir"
                       placeholder="Ingrese el temario de la reunión...">
                 </textarea>
                 <div class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2" x-text="contenido"></div>
             </div>

             {{-- 2. ACUERDO --}}
             <div x-data="{ contenido: '' }">
                 <label for="acuerdo" class="block font-bold text-gray-700 mb-2">Acuerdo:</label>   
                 <textarea 
                       x-model="contenido"
                       id="acuerdo" 
                       name="acuerdo" 
                       rows="4" 
                       class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir"
                       placeholder="Ingrese el acuerdo de la reunión...">
                 </textarea>
                 <div class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2" x-text="contenido"></div>
             </div>

             {{-- 3. OBSERVACIONES --}}
             <div x-data="{ contenido: '' }">
                 <label for="observaciones" class="block font-bold text-gray-700 mb-2">Observaciones:</label>   
                 <textarea 
                       x-model="contenido"
                       id="observaciones" 
                       name="observaciones" 
                       rows="4" 
                       class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir"
                       placeholder="Ingrese las observaciones de la reunión...">
                 </textarea>
                 <div class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2" x-text="contenido"></div>
             </div>

            </div>      
            
            {{-- SECCIÓN 4: Próxima Reunión --}}
            <div class="mt-8 flex items-center gap-3">
                <label for="proxima_reunion" class="font-bold text-gray-700">Próxima Reunión:</label>
                <input 
                    type="date" 
                    id="proxima_reunion" 
                    name="proxima_reunion"
                    class="border border-gray-300 rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

             <div>
				<label class="mt-6 block">
					Firmas: 
				</label>
			</div>
            
            {{-- SECCIÓN 5: Botonera --}}
            <div class="mt-10 pt-6 border-t border-gray-200">
                 <div class="fila-botones justify-between items-center">
                    <div class="flex gap-3">
                        <button type="button" class="btn-eliminar">Eliminar</button>
                        
                        <button type="button" class="btn-gris-variantes" onclick="window.print()">
                           Vista Previa
                        </button>

                        <button type="button" class="btn-aceptar">Descargar</button>

                        <button type="submit" class="btn-aceptar">Guardar</button>
                       
                        <a href="#" class="btn-volver">Volver</a>
                    </div>  
                 </div>
            </div>

        </div>
    </div>
 </form>
@endsection
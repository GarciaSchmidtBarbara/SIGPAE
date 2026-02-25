@extends('layouts.base')


@section('encabezado', 'Acta Reunión de Trabajo - Equipo Directivo - EI - Docentes (Banda)')

@section('contenido')
@php
      
        $esEdicion   = isset($planilla);
        $soloLectura = $soloLectura ?? false;
        $datos = $esEdicion ? ($planilla->datos_planilla ?? []) : [];

        // Participantes:
        // - en crear viene $personal desde el controlador
        // - en editar/ver los leo del JSON
        $listadoParticipantes = $esEdicion
            ? ($datos['participantes'] ?? [])
            : ($personal ?? []);

        $proximaValue = $datos['proxima_reunion'] ?? null;
    @endphp
    <form
        method="POST"
        action="{{ $esEdicion
                    ? route('planillas.actualizar', $planilla->id_planilla)
                    : route('planillas.acta-reuniones-banda.store') }}"
    ></form>
    {{-- FORMULARIO: Apunta a la ruta store de BANDA --}}
    @csrf
     @if($esEdicion)
            @method('PUT')
    @endif

    <div class="max-w-4xl mx-auto mt-6 px-4">
        <div class="bg-white p-8 rounded-lg shadow-lg border border-gray-200">

            <div class="flex flex-wrap items-center justify-center gap-6 mb-8">
                <div class="flex items-center gap-2">
                    <label 
                      for="grado" class="font-bold text-gray-700">Grado:</label>
                    <input 
                        type="text" 
                        id="grado" 
                        name="grado" 
                        value="{{ old('grado', $datos['grado'] ?? '') }}"
                        class="border border-gray-300 rounded px-2 py-1 w-16 text-center" 
                        {{ $soloLectura ? 'readonly disabled' : '' }}
                        placeholder="1 A">
                </div>
                <div class="flex items-center gap-2">
                    <label for="fecha" class="font-bold text-gray-700">Fecha:</label>
                    <input 
                        type="date" 
                        id="fecha" 
                        name="fecha" 
                        value="{{ old('fecha', $datos['fecha'] ?? now()->format('Y-m-d')) }}"
                        class="border border-gray-300 rounded px-2 py-1 text-gray-600">
                         {{ $soloLectura ? 'readonly disabled' : '' }}>
                </div>
                <div class="flex items-center gap-2">
                    <label for="hora" class="font-bold text-gray-700">Hora:</label>
                    <input 
                    type="time" 
                    id="hora" 
                    name="hora" 
                    value="{{ old('hora', $datos['hora'] ?? '') }}"
                    class="border border-gray-300 rounded px-2 py-1 text-gray-600"
                     {{ $soloLectura ? 'readonly disabled' : '' }}>
                </div>
            </div>

          {{-- PARTICIPANTES --}}
                <x-tabla-participantes
                    :listado="$listadoParticipantes"
                    :soloLectura="$soloLectura"
                />


            {{-- SECCIÓN 3: Textareas (Con truco de impresión) --}}
            <div class="mt-8 space-y-6">    

             {{-- Temario --}}
            <div x-data="{ contenido: @js(old('temario', $datos['temario'] ?? '')) }">
                 <label for="temario" class="block font-bold text-gray-700 mb-2">Temario:</label> 
                 <textarea 
                    x-model="contenido" 
                    id="temario" 
                    name="temario" 
                    rows="4" 
                    class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir" 
                    {{ $soloLectura ? 'readonly disabled' : '' }}
                    placeholder="Ingrese el temario..."></textarea>
                 <div 
                 class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2" 
                 x-text="contenido"></div>
             </div>

             {{-- Acuerdo --}}
             <div x-data="{ contenido: @js(old('acuerdo', $datos['acuerdo'] ?? '')) }">
                 <label for="acuerdo" class="block font-bold text-gray-700 mb-2">Acuerdo:</label>   
                 <textarea 
                   x-model="contenido" 
                   id="acuerdo" 
                   name="acuerdo" 
                   rows="4" 
                   class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir" 
                   {{ $soloLectura ? 'readonly disabled' : '' }}
                   placeholder="Ingrese el acuerdo..."></textarea>
                 <div 
                 class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2" 
                 x-text="contenido"></div>
             </div>

             {{-- Observaciones --}}
             <div x-data="{ contenido: @js(old('observaciones', $datos['observaciones'] ?? '')) }">
                 <label for="observaciones" class="block font-bold text-gray-700 mb-2">Observaciones:</label>   
                 <textarea 
                    x-model="contenido" 
                    id="observaciones" 
                    name="observaciones" 
                    rows="4" 
                    class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir" 
                    {{ $soloLectura ? 'readonly disabled' : '' }}
                    placeholder="Ingrese observaciones..."></textarea>
                 <div 
                 class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2" 
                 x-text="contenido"></div>
             </div>

            </div>      
            
            {{-- SECCIÓN 4: Próxima Reunión --}}
            <div class="mt-8 flex items-center gap-3">
                <label for="proxima_reunion" class="font-bold text-gray-700">Próxima Reunión:</label>
                <input 
                    type="date" 
                    id="proxima_reunion" 
                    name="proxima_reunion" 
                    value="{{ old('proxima_reunion', $proximaValue ?? now()->format('Y-m-d')) }}"
                    class="border border-gray-300 rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    {{ $soloLectura ? 'readonly disabled' : '' }}>

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
                        @unless($soloLectura)
                        <button type="button" class="btn-eliminar">Eliminar</button>
                        <button type="submit" class="btn-aceptar">Guardar</button>
                        @endunless
                        <button type="button" class="btn-gris-variantes" onclick="window.print()">Vista Previa</button>
                        <button type="button" class="btn-aceptar onclick="window.print()">Descargar</button>
                       
                        <a href="{{ route('planillas.principal') }}" class="btn-volver">Volver</a>
                    </div>  
                 </div>
            </div>

        </div>
    </div>
 </form>
  @if(!empty($autoImprimir))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
@endsection
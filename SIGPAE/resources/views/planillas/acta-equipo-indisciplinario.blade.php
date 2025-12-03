@extends('layouts.base')

@section('encabezado', 'Acta Reunión Equipo Interdisciplinario - Equipo Directivo')

@section('contenido')
  @php
    $soloLectura = $soloLectura ?? false;   // viene desde el controller (ver / descargar)
    $esEdicion   = isset($planilla);       // si hay planilla, es editar/ver
    $datos       = $datos ?? ($planilla->datos_planilla ?? []);  // atajo
	$proximaValue = $datos['proxima_reunion'] ?? null;
	   if ($proximaValue) {
        if (str_contains($proximaValue, '/')) {
            try {
                $proximaValue = \Carbon\Carbon::createFromFormat('d/m/Y', $proximaValue)->format('Y-m-d');
            } catch (\Exception $e) {
                
            }
        }
    }
@endphp

    <form 
    method="POST"
    action="{{ $esEdicion 
                ? route('planillas.actualizar', $planilla->id_planilla) 
                : route('planillas.acta-equipo-indisciplinario.store') }}"
>
    @csrf
    @if($esEdicion)
        @method('PUT')
    @endif

	<div class="max-w-4xl mx-auto mt-6 px-4">
		<div class="bg-white p-8 rounded-lg shadow-lg border border-gray-200">

			{{-- SECCIÓN 1: Inputs Superiores --}}
			<div class="flex flex-wrap items-center justify-center gap-6 mb-8">
				
			     {{-- Grado, Fecha, Hora --}}
				<div class="flex items-center gap-2">
					<label for="grado" class="font-bold text-gray-700">Grado:</label>
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
					 type="text"
					 id="fecha"
					 name="fecha"
					 value="{{ old('fecha', $datos['fecha'] ?? '') }}"
					 class="border border-gray-300 rounded px-2 py-1 text-gray-600"
                     {{ $soloLectura ? 'readonly disabled' : '' }}
					 placeholder="dd/mm/aaaa">
				</div>
				<div class="flex items-center gap-2">
					<label for="hora" class="font-bold text-gray-700">Hora:</label>
					<input 
					  type="text" 
					  id="hora" 
					  name="hora" 
					  value="{{ old('hora', $datos['hora'] ?? '') }}"
					  class="border border-gray-300 rounded px-2 py-1 text-gray-600"
					  {{ $soloLectura ? 'readonly disabled' : '' }}
    				  placeholder="--:--">
				</div>
			</div>

							{{-- LÓGICA INTELIGENTE: ¿Es Edición o Creación? --}}
				@php
					if (isset($planilla)) {
						$datosParaTabla = $planilla->datos_planilla['participantes'];
					} else {
						$datosParaTabla = $personal;
					}
				@endphp

				{{-- Le pasamos la variable calculada al componente --}}
				<x-tabla-participantes :listado="$datosParaTabla" />
		     <div class="mt-8 space-y-6">    

             {{-- 1. TEMARIO --}}
            {{-- 1. TEMARIO --}}
			<div 
				x-data="{
					contenido: @js(old('temario', $datos['temario'] ?? ''))
				}"
			>
				<label for="temario" class="block font-bold text-gray-700 mb-2">Temario:</label> 
				
				{{-- TEXTAREA (solo en pantalla) --}}
				<textarea 
					x-model="contenido"
					id="temario" 
					name="temario" 
					rows="4" 
					class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir"
					{{ $soloLectura ? 'readonly disabled' : '' }}
					placeholder="Ingrese el temario de la reunión...">@{{ '' }}</textarea>

				{{-- VERSIÓN PARA IMPRESIÓN --}}
				<div class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2"
					x-text="contenido">
				</div>
			</div>


             {{-- 2. ACUERDO --}}
			<div 
				x-data="{
					contenido: @js(old('acuerdo', $datos['acuerdo'] ?? ''))
				}"
			>
				<label for="acuerdo" class="block font-bold text-gray-700 mb-2">Acuerdo:</label>   
				
				<textarea 
					x-model="contenido"
					id="acuerdo" 
					name="acuerdo" 
					rows="4" 
					class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir"
					{{ $soloLectura ? 'readonly disabled' : '' }}
					placeholder="Ingrese el acuerdo de la reunión...">@{{ '' }}</textarea>

				<div class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2"
					x-text="contenido">
				</div>
			</div>


            {{-- 3. OBSERVACIONES --}}
			<div 
				x-data="{
					contenido: @js(old('observaciones', $datos['observaciones'] ?? ''))
				}"
			>
				<label for="observaciones" class="block font-bold text-gray-700 mb-2">Observaciones:</label>   
				
				<textarea 
					x-model="contenido"
					id="observaciones" 
					name="observaciones" 
					rows="4" 
					class="w-full border border-gray-300 rounded px-2 py-1 no-imprimir"
					{{ $soloLectura ? 'readonly disabled' : '' }}
					placeholder="Ingrese las observaciones de la reunión...">@{{ '' }}</textarea>

				<div class="solo-imprimir text-justify whitespace-pre-wrap border-b border-gray-300 pb-2"
					x-text="contenido">
				</div>
			</div>

            </div>
            
			<div class="mt-8 flex items-center gap-3">
				<label for="proxima_reunion" 
				       class="font-bold text-gray-700">Próxima Reunión:</label>
				<input 
				    type="date" 
					id="proxima_reunion" 
					name="proxima_reunion"
					value="{{ old('proxima_reunion', $proximaValue) }}"
					class="border border-gray-300 rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
					{{ $soloLectura ? 'readonly disabled' : '' }}>
			</div>
            <div>
				<label class="mt-6 block">
					Firmas: 
				</label>
			</div>

			<div class="mt-10 pt-6 border-t border-gray-200">
                 <div class="fila-botones justify-between items-center">
					

					<div class="flex gap-3">
						<button type="button" class="btn-eliminar">
							Eliminar
						</button>
						
						<button 
						    type="button" 
						    class="btn-gris-variantes"
							onclick="window.print()">
						   Vista Previa
						</button>

						<button 
						     type="button" 
							 class="btn-aceptar"
						    >
                            Descargar
                        </button>

						<button type="submit" class="btn-aceptar">
                            Guardar
                        </button>
                       
						<a href="{{ route('planillas.principal') }}" class="btn-volver">
                            Volver
                        </a>

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
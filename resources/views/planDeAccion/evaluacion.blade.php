@extends('layouts.base')

@section('encabezado', 'Evaluación de Resultados')

@section('contenido')

<div class="p-6 max-w-5xl mx-auto">

    <div class="bg-gray-50 border rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-gray-700 mb-2">Datos del Plan</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <label class="text-gray-500">Tipo de Plan</label>
                <input type="text" class="input-disabled" 
                       value="{{ $plan->tipo_plan }}" readonly>
            </div>

            <div>
                <label class="text-gray-500">Estado</label>
                <input type="text" class="input-disabled" 
                       value="{{ $plan->estado_plan }}" readonly>
            </div>

            <div>
                <label class="text-gray-500">Fecha creación</label>
                <input type="text" class="input-disabled" 
                       value="{{ $plan->created_at->format('d/m/Y') }}" readonly>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('planDeAccion.guardarEvaluacion', $plan->id_plan_de_accion) }}">
        @csrf

        <div class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label>Criterios *</label>
                    <textarea name="criterios" rows="3"
                        class="input-area w-full p-2 border border-gray-300 rounded-md required"></textarea>
                </div>
            </div>
            

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3"
                        class="input-area w-full p-2 border border-gray-300 rounded-md"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ">
                <div class="col-span-1 md:col-span-2  ">
                    <label class="font-semibold">Conclusiones *</label>
                    <textarea name="conclusiones" rows="3"
                        class="input-area w-full p-2 border border-gray-300 rounded-md required"></textarea>
                </div>
            </div>

        </div>

        <div class="flex justify-end gap-3 mt-8">
            <a href="{{ route('planDeAccion.principal') }}" 
               class="btn-volver">
                Cancelar
            </a>

            <button type="submit" class="btn-aceptar">Guardar Evaluación</button>
        </div>

    </form>

</div>

@endsection
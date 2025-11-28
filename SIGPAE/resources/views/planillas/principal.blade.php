@extends('layouts.base')

@section('encabezado', 'Planillas')

@section('contenido')
    <div class="fila-botones mt-8 mb-6">
        <a class="btn-volver" href="{{ route('welcome') }}">Volver a Principal</a>
    </div>

    <div class="flex flex-row justify-start gap-4">

        <div x-data="{ abrirPlanilla:false, tipo:'' }" x-cloak>

            <x-boton-crear class="btn-primario" @click="abrirPlanilla = true">
                Crear
            </x-boton-crear>

            {{-- Abrir el modal --}}
            <x-ui.modal x-data="{ open: false }"
                x-effect="open = abrirPlanilla; if (!open && abrirPlanilla) abrirPlanilla = false" @click.stop
                title="Elija el documento a crear" size="lg" :closeOnBackdrop="true">

                <select x-model="tipo" class="mt-1 block w-full rounded-lg border-gray-300">

                    <option value="" disabled selected>— Seleccione una opción —</option>
                    <option value="acta-trabajo">Acta reunión de trabajo</option>
                    <option value="acta-equipo">Acta reunión de Equipo (indisciplinario)</option>
                    <option value="acta-banda">Acta reuniones de Banda</option>
                    <option value="planilla-medial">Planilla Medial [año]</option>
                </select>


                <x-slot:footer>
                    <div class="w-full flex justify-between gap-6" @click.stop>
                        <button class="btn-eliminar" @click="abrirPlanilla = false">
                            Cancelar
                        </button>

                        <button class="btn-aceptar" type="button"
                            :class="tipo ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                            :disabled="!tipo" @click="
                                    if (tipo === 'acta-trabajo')   { window.location = '{{ route('planillas.acta-reunion-trabajo.create') }}' }
                                    if (tipo === 'acta-equipo')    { window.location = '{{ route('planillas.acta-equipo-indisciplinario.create') }}' }
                                    if (tipo === 'acta-banda')     { window.location = '{{ route('planillas.acta-reuniones-banda.create') }}' }
                                    if (tipo === 'planilla-medial'){ window.location = '{{ route('planillas.planilla-medial.create') }}' }
                                ">
                            Aceptar
                        </button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        </div>

        <x-boton-buscar>Buscar</x-boton-buscar>
    </div>

    <div class="container mt-4">
        <table class="table border-collapse border border-slate-400 w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-slate-300 p-2">Nombre</th>
                    <th class="border border-slate-300 p-2">Tipo</th>
                    <th class="border border-slate-300 p-2">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 7; $i++)
                    <tr>
                        <!-- dentro de este FOR se generan las planillas en base de datos -->
                        <!-- también agregar un tacho de basura para borrar -->
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
@endsection
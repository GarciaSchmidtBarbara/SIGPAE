@extends('layouts.base')

@section('encabezado', 'Planillas')

@section('contenido')
<div class="flex flex-row justify-start gap-4">

    <x-boton-crear>Crear</x-boton-crear>     
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
                </!-- --- dentro de este FOR se generan las planillas en base de datos --->   
                </!-- --- tambien agregar una tacho de basura pegado a la planilla para borrar --->   

            </tr>
            @endfor
        </tbody>
    </table>
</div>

@endsection

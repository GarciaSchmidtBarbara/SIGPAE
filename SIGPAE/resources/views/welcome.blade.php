@extends('layouts.base')

@section('encabezado', 'Bienvenido ' . auth()->user()->name)

@section('contenido')
<div class="grid grid-cols-5 gap-6">

    {{--Notificaciones / Eventos del día --}}
    <div class="col-span-3 space-y-6">

        <h2 class="text-2xl font-semibold text-gray-800">Eventos del día</h2>

        <div class="grid grid-cols-3 gap-4">
            {{-- ejemplos --}}
            <div class="p-4 bg-white shadow rounded-lg">
                <p class="font-semibold">Notificación 1</p>
                <p class="text-sm text-gray-600">Detalle del evento...</p>
            </div>

            <div class="p-4 bg-white shadow rounded-lg">
                <p class="font-semibold">Notificación 2</p>
                <p class="text-sm text-gray-600">Detalle del evento...</p>
            </div>

            <div class="p-4 bg-white shadow rounded-lg">
                <p class="font-semibold">Notificación 3</p>
                <p class="text-sm text-gray-600">Detalle del evento...</p>
            </div>
        </div>

    </div>

    {{-- Calendario + Próximos eventos --}}
    <div class="col-span-2 space-y-6">

        {{-- CALENDARIO --}}
        <div class="p-4 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Calendario</h2>

            {{-- Google Calendar / FullCalendar --}}
            <div class="h-64 flex items-center justify-center border rounded-lg text-gray-500">
                Calendario aquí
            </div>
        </div>

        @if (!auth()->user()->google_refresh_token)
            <div class="p-4 bg-red-100 border border-red-300 shadow rounded-lg text-center">
                <h3 class="text-lg font-semibold text-red-800 mb-2">
                    ⚠️ Sincronización Necesaria
                </h3>
                <p class="text-sm text-red-700 mb-4">
                    Conecta tu cuenta de Google para habilitar la sincronización de eventos y el envío de correos.
                </p>
                <a href="{{ route('google.login') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.81-4.75h1.76v-1.16H7.19a4.26 4.26 0 0 1 0-3.32h4.51V7.19H7.19c.12-.46.33-.87.62-1.23l1.11-1.11L8.38 4.75l-1.11 1.11c-.3.37-.51.78-.63 1.23h1.76v1.16H7.19c-.12.46-.33.87-.62 1.23l-1.11 1.11.78.78 1.11-1.11c.3-.37.51-.78.63-1.23h1.76v1.16H7.19a4.26 4.26 0 0 1 0 3.32z"/></svg>
                    Conectar con Google Calendar
                </a>
            </div>
        @else
            <div class="p-4 bg-green-100 border border-green-300 shadow rounded-lg text-center">
                <h3 class="text-lg font-semibold text-green-800 mb-2">
                    ✅ Conexión Activa
                </h3>
                <p class="text-sm text-green-700">
                    Tu cuenta está sincronizada.
                </p>
            </div>
        @endif

        {{-- PRÓXIMOS EVENTOS --}}
        <div class="p-4 bg-white shadow rounded-lg">
            <h2 class="text-xl font-semibold mb-3 text-gray-800">Próximos eventos</h2>
        </div>

    </div>

</div>
@endsection

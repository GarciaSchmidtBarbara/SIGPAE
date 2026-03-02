@extends('layouts.base')

@section('encabezado', 'Usuarios')

@section('contenido')

<div class="p-6" x-data="estadoUsuario()" 
     @abrir-modal-estado.window="abrir($event.detail)">

    {{-- 🔹 BARRA SUPERIOR --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">

        {{-- BUSCADOR --}}
        <form action="{{ route('usuarios.principal') }}" method="GET" 
              class="w-full md:w-1/2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search"
                       name="buscar"
                       value="{{ request('buscar') }}"
                       placeholder="Buscar por nombre, apellido o documento..."
                       class="block w-full p-3 pl-10 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </form>

        {{-- BOTÓN CREAR --}}
        <a href="{{ route('usuarios.crear-editar') }}" 
           class="btn-aceptar">
            + Registrar Usuario
        </a>
    </div>

    {{-- 🔹 TABLA --}}
    <div >

        @if($usuarios->count() > 0)

            <div class="overflow-x-auto">
                <table class="modern-table w-full">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Profesión</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                            <tr class="hover:bg-indigo-50/30 transition duration-150 ease-in-out">

                                <td class="p-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $usuario->persona->nombre }} {{ $usuario->persona->apellido }}
                                    </div>
                                </td>

                                <td class="p-4 text-sm text-gray-600">
                                    {{ $usuario->persona->dni }}
                                </td>

                                <td class="p-4 text-sm text-gray-600">
                                    {{ $usuario->siglas }} - {{ $usuario->profesion}}
                                </td>

                                {{-- ESTADO VISUAL --}}
                                <td class="p-4">
                                    @if($usuario->persona->activo)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 border border-green-200">
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 border border-red-200">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                <td class="p-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        {!! view('components.boton-estado', [
                                            'activo' => $usuario->persona->activo,
                                            'route' => route('usuarios.cambiarActivo', $usuario->id_profesional),
                                            'text_activo' => 'Desactivar',
                                            'text_inactivo' => 'Activar',
                                            'message_activo' => '¿Desea desactivar este usuario?',
                                            'message_inactivo' => '¿Desea activar este usuario?',
                                        ])->render() !!}
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            <div class="p-4 border-t border-gray-200">
                {{ $usuarios->links() }}
            </div>

        @else
            {{-- ESTADO VACÍO --}}
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    No hay usuarios registrados
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Comienza creando un nuevo usuario desde el botón superior.
                </p>
            </div>
        @endif

    </div>
</div>
@endsection
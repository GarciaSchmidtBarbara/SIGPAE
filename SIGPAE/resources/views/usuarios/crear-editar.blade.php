@extends('layouts.base')
@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <strong>Error:</strong>
    <ul class="mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Usuario' : 'Crear Usuario')

@section('contenido')
<div x-data="{
    // Profesional form helpers
    selectedProfesion: '{{ $usuarioData['profesion'] ?? '' }}',
    selectedSigla: '{{ $usuarioData['siglas'] ?? '' }}',
    onProfesionChange(e) {
        const opt = e.target.options[e.target.selectedIndex];
        this.selectedProfesion = opt.value || '';
        this.selectedSigla = opt.dataset.sigla || '';
    },
}">

    <form method="POST" action="{{ isset($modo) && $modo === 'editar' 
            ? route('usuarios.update', $usuario->id_profesional)
            : route('usuarios.store') }}">
        @csrf
        @if(isset($modo) && $modo === 'editar')
            @method('PUT')
        @endif
        <div class="space-y-8 mb-6">
            <p class="separador">Información Personal del Usuario</p>
            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Documento" required />
                    <input name="dni" value="{{ $usuarioData['dni'] ?? old('dni') }}" placeholder="Documento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nombres" required />
                    <input name="nombre" value="{{ $usuarioData['nombre'] ?? old('nombre') }}" placeholder="Nombres" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Apellidos" required />
                    <input name="apellido" value="{{ $usuarioData['apellido'] ?? old('apellido') }}" placeholder="Apellidos" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <x-campo-fecha-edad label="Fecha de nacimiento" name="fecha_nacimiento" :value="old(
                        'fecha_nacimiento',
                        isset($usuario) && isset($usuario->persona->fecha_nacimiento)
                            ? \Illuminate\Support\Carbon::parse($usuario->persona->fecha_nacimiento)->format('Y-m-d')
                            : ($usuarioData['fecha_nacimiento'] ?? '')
                    )" edad-name="edad" :edad-value="old('edad', $usuarioData['edad'] ?? '')" required

                />

                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nombre de Usuario" required />
                    <input name="usuario" value="{{ $usuarioData['usuario'] ?? old('usuario') }}" placeholder="nombre_de_usuario" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Profesión" required />
                    <select name="profesion" @change="onProfesionChange" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccione profesión</option>
                        @foreach(\App\Enums\Siglas::cases() as $case)
                            @php $label = $case->label(); $sigla = $case->value; @endphp
                            <option value="{{ $label }}" data-sigla="{{ $sigla }}" {{ (isset($usuarioData['profesion']) && $usuarioData['profesion'] === $label) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Siglas" required />
                    <input name="siglas" readonly x-model="selectedSigla" value="{{ $usuarioData['siglas'] ?? old('siglas') }}" placeholder="Seleccione profesión" class="border px-2 py-1 rounded bg-gray-100"/>
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Email" required />
                    <input name="email" value="{{ $usuarioData['email'] ?? old('email') }}" placeholder="email@.com" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Contraseña" required />
                    <input name="contrasenia" value="{{ $usuarioData['contrasenia'] ?? old('contrasenia') }}" placeholder="Contr4S3ñ4_S3gur4" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Confirmar Contraseña" required />
                    <input name="confirmar-contrasenia" value="{{ $usuarioData['confirmar-contrasenia'] ?? old('confirmar-contrasenia') }}" placeholder="Contr4S3ñ4_S3gur4" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="space-y-8">
        </div>


        <div class="fila-botones mt-8">
            <button type="submit" class="btn-aceptar">Guardar</button>
            <a class="btn-volver" href="{{ route('usuarios.principal') }}" >Volver</a>
        </div>
    </form>
</div>
@endsection
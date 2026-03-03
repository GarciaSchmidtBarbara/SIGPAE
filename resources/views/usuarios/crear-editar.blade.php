@extends('layouts.base')

@section('encabezado', 'Crear Usuario')

@section('contenido')

<div class="max-w-4xl mx-auto px-4 space-y-4">

    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-semibold text-gray-800">
            Complete los siguientes campos requeridos
        </h2>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}" x-data="usuarioForm()" @submit.prevent="validarYGuardar($event)"> 
        @csrf 
        <section class="space-y-4 border-b pb-4">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="">
                    <x-campo-requerido class="label-perfil" text="Nombres" required />
                    <input name="nombre" value="{{ $usuarioData['nombre'] ?? old('nombre') }}"
                        @input="limpiarError('nombre')"
                        class="input-perfil">
                    <div x-show="errors.nombre" x-text="errors.nombre" class="text-xs text-red-600 mt-1"></div>
                </div>

                <div class="">
                    <x-campo-requerido  class="label-perfil" text="Apellidos" required />
                    <input name="apellido" value="{{ $usuarioData['apellido'] ?? old('apellido') }}"
                        @input="limpiarError('apellido')"
                        class="input-perfil">
                    <div x-show="errors.apellido" x-text="errors.apellido" class="text-xs text-red-600 mt-1"></div>
                </div>

                <div class="">
                    <x-campo-requerido  class="label-perfil" text="Documento" required />
                    <input name="dni" value="{{ $usuarioData['dni'] ?? old('dni') }}"
                        @input="limpiarError('dni')"
                        class="input-perfil">
                    <div x-show="errors.dni" x-text="errors.dni" class="text-xs text-red-600 mt-1"></div>
                </div>

                <div class="">
                    <x-campo-requerido class="label-perfil" text="Email" required />
                    <input name="email" value="{{ $usuarioData['email'] ?? old('email') }}"
                        @input="limpiarError('email')"
                        class="input-perfil">
                    <div x-show="errors.email" x-text="errors.email" class="text-xs text-red-600 mt-1"></div>
                </div>
            </div>
        </section>

        <div class="flex justify-end mt-6 gap-6"> 
            <button type="submit" class="btn-aceptar">Guardar</button> 
            <a class="btn-volver" href="{{ route('usuarios.principal') }}" >Volver</a> 
        </div>
    </form>
</div>

<script>
    function usuarioForm() {
        return {
            errors: { nombre: '', apellido: '', dni: '', email: '' },

            limpiarError(campo) {
                this.errors[campo] = '';
            },

            validarYGuardar(event) {
                this.errors = { nombre: '', apellido: '', dni: '', email: '' };
                let hayError = false;
                const form = event.target;

                if (!form.querySelector('[name=nombre]')?.value?.trim()) {
                    this.errors.nombre = 'Debe ingresar el nombre'; hayError = true;
                }
                if (!form.querySelector('[name=apellido]')?.value?.trim()) {
                    this.errors.apellido = 'Debe ingresar el apellido'; hayError = true;
                }
                if (!form.querySelector('[name=dni]')?.value?.trim()) {
                    this.errors.dni = 'Debe ingresar el documento'; hayError = true;
                } else if (!/^\d+$/.test(form.querySelector('[name=dni]').value.trim())) {
                    this.errors.dni = 'El documento debe contener solo números'; hayError = true;
                }
                const email = form.querySelector('[name=email]')?.value?.trim();
                if (!email) {
                    this.errors.email = 'Debe ingresar el email'; hayError = true;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    this.errors.email = 'El email no tiene un formato válido'; hayError = true;
                }

                if (!hayError) form.submit();
            }
        };
    }
</script>
@endsection
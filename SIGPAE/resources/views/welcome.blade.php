@extends('layouts.base')

@section('encabezado', 'Ejemplo de Componentes')

@section('contenido')
    <div class="space-y-8">
        <!-- Sección de botones -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Botones</h2>
            <div class="flex gap-4">
                <button class="btn-aceptar">Guardar</button>
                <button class="btn-eliminar">Eliminar mi cuenta</button>
            </div>
        </div>

        <!-- #region -->
        <!-- #probando ALPINE y funcionaaaa jejej !!!!! -->
        <div x-data="{ count: 0 }" class="p-4">
            <button class="btn-aceptar" @click="count++">Sumar</button>
            <span class="ml-3">Count: <strong x-text="count"></strong></span>
        </div>


        <div x-data="{ open:false }" class="p-6 space-y-4">
            <button class="btn-aceptar" @click="open = true">
                Probando modal
            </button>
            <x-ui.modal title="Confirmar acción" size="md">
                <p class="text-gray-700">¿Seguro que querés hacer esto?</p>

                <x-slot:footer>
                    <button class="btn-eliminar" @click="open=false">Cancelar
                    </button>
                    <button class="btn-aceptar" @click="open=false">Confirmar
                    </button>
                </x-slot:footer>
            </x-ui.modal>
        </div>


        <!-- #MENSAJE DE ERROR AQUI SE PROBO CON BOTON 
                          (solo cambiar texto en mensaje=" ")-->
        <div x-data="{ open:false }">
            <button class="btn-eliminar" @click="open = true">mensaje error</button>
            <x-ui.modal-error title="ERROR" message="No se encontraron alumnos con los criterios de búsqueda específicos"
                variant="error" />
        </div>

        <!-- Alumno Eliminado(tipo check list)-->
        <div x-data="{ open:false }">
            <button class="btn-eliminar" @click="open = true">Mensaje éxito</button>
            <x-ui.modal-alert title="Listo" message="Alumno eliminado" variant="succes" />
        </div>

        <!-- Confirmar / Cancelar-->

        <div x-data="{ open:false, alumnoId: 1 }">
            <button class="btn-eliminar" @click="open = true">Eliminar Alumno</button>

            <x-ui.modal-confirmar message="¿Desea eliminar el alumno?" confirmText="Confirmar" cancelText="Cancelar"
                formId="formEliminarAlumno" :centerButtons="true" />

            <form id="formEliminarAlumno" method="POST" :action="`{{ url('/alumnos') }}/${alumnoId}`">
                @csrf
                @method('DELETE')
            </form>
        </div>



        @php
            $intervenciones = ['Psicopedagógica', 'Social', 'Académica', 'Familiar'];
        @endphp

        <!-- Sección de checkboxes vertical -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección múltiple (Vertical)</h2>
            <x-checkboxes :items="$intervenciones" name="tipo_intervencion_vertical" layout="vertical" />
        </div>

        <!-- Sección de checkboxes horizontal -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección múltiple (Horizontal)</h2>
            <x-checkboxes :items="$intervenciones" name="tipo_intervencion_horizontal" layout="horizontal" />
        </div>

        <!-- Sección de radio buttons vertical -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección única (Vertical)</h2>
            <x-opcion-unica :items="$intervenciones" name="tipo_intervencion_unica_vertical" layout="vertical" />
        </div>

        <!-- Sección de radio buttons horizontal -->
        <div class="space-y-4">
            <h2 class="titulo-seccion">Selección única (Horizontal)</h2>
            <x-opcion-unica :items="$intervenciones" name="tipo_intervencion_unica_horizontal" layout="horizontal" />
        </div>
    </div>
@endsection
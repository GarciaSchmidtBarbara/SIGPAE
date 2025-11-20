@extends('layouts.base')

@section('encabezado', isset($modo) && $modo === 'editar' ? 'Editar Alumno' : 'Crear Alumno')

@section('contenido')

{{-- Mensajes de estado --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

@php
    $esEdicion = isset($modo) && $modo === 'editar' && isset($alumno);
    $inactivo = $esEdicion ? ($alumno->persona->activo === false) : false;
@endphp

<div class="mt-4 my-4 flex justify-between items-center">
    <div class="text-sm text-red-600 min-h-[1.5rem]">
        @if($esEdicion && $inactivo)
            <p>Este alumno está inactivo. No se permiten modificaciones.</p>
        @endif
    </div>
    <div class="flex justify-end space-x-4">
        @if($esEdicion)
            <x-boton-estado 
                :activo="$alumno->persona->activo" 
                :route="route('alumnos.cambiarActivo', $alumno->id_alumno)" 
            />
        @endif
        <a class="btn-volver" href="{{ route('alumnos.principal') }}">Volver</a>
    </div>
</div>



<div x-data="{
    familiares: {{ json_encode(session('asistente.familiares', [])) }},

    alumnoData: {{ json_encode(session('asistente.alumno', [])) }},
    
    errors: {
        dni: '',
        nombre: '',
        apellido: '',
        nacionalidad: '',
        aula: '',
        inasistencias: '',
        fecha_nacimiento: ''
    },

    limpiarError(campo) {
        if (this.errors[campo]) {
            this.errors[campo] = '';
        }
    },

    async validarYGuardar() {
        this.errors = {}; 
        this.dniError = '';
        
        // Usamos UNA sola variable para todo el proceso
        let hayErrores = false; 
        
        // 1. Validación Local (Campos Vacíos)
        const requeridos = ['dni', 'nombre', 'apellido', 'aula', 'inasistencias', 'fecha_nacimiento'];

        requeridos.forEach(campo => {
            // Si falta un campo, marcamos error y levantamos la bandera
            if (!this.alumnoData[campo] || String(this.alumnoData[campo]).trim() === '') {
                this.errors[campo] = 'Este campo es requerido.';
                hayErrores = true; 
            }
        });

        // 2. Validación Remota de DNI
        // Solo validamos si el usuario escribió algo en el DNI
        if (this.alumnoData.dni) {
            try {
                const response = await fetch('{{ route("alumnos.validar-dni") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        dni: this.alumnoData.dni,
                        id_alumno: this.alumnoData.id_alumno 
                    })
                });

                if (!response.ok) throw new Error('Error de red');

                const data = await response.json();

                if (!data.valid) {
                    // Si el servidor dice que está duplicado, mostramos el mensaje
                    this.errors.dni = data.message; 
                    hayErrores = true; // ¡Importante! Levantamos la misma bandera
                }

            } catch (e) {
                console.error(e);
                alert('Error técnico al validar DNI.');
                return; // Frenamos por error de sistema
            }
        }

        // 3. DECISIÓN FINAL
        // Si la bandera está levantada (por vacío O por duplicado), no enviamos.
        if (hayErrores) {
            return;
        }

        // 4. ÉXITO
        this.$refs.form.submit();
    },
    
    async gestionarEliminacion(indice, tipo) {
        const confirmMsg = tipo === 'familiar' 
            ? '¿Estás seguro de eliminar este familiar?' 
            : '¿Estás seguro de desvincular este hermano alumno?';

        if (confirm(confirmMsg)) {
            try {
                // ¡LA MAGIA! Pasamos el 'tipo' como un query parameter en la URL
                const response = await fetch(`{{ url('/alumnos/asistente/item/eliminar') }}/${indice}?tipo=${tipo}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.familiares.splice(indice, 1);
                } else {
                    alert('Error al eliminar el item.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el item.');
            }
        }
    },

    /**
     * Sincroniza el estado (envía JSON al servidor) y luego redirige.
     */
    async sincronizarEstado(rutaDestino) {
        const estado = {
            alumno: this.alumnoData,
            familiares: this.familiares
        };

        try {
            const response = await fetch('{{ route("asistente.sincronizar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(estado)
            });

            if (!response.ok) throw new Error('Error al sincronizar');

            window.location.href = rutaDestino;

        } catch (error) {
            console.error(error);
            alert('Error al guardar los datos. Intente de nuevo.');
        }
    },

    /**
     * Prepara la URL de edición y llama al sincronizador
     */
    prepararEdicionFamiliar(indice) {
        // Usamos url() de base y le pegamos el índice con JS
        const urlDestino = `{{ url('/familiares') }}/${indice}/editar`;
        this.sincronizarEstado(urlDestino);
    },

    /**
     * Prepara la URL de creación y llama al sincronizador
     */
    prepararCreacionFamiliar() {
        this.sincronizarEstado('{{ route("familiares.crear") }}');
    }
}">
    
    <form method="POST" action="{{ isset($modo) && $modo === 'editar' ? route('alumnos.actualizar', $alumno->id_alumno) : route('alumnos.store') }}"
            x-ref="form" novalidate>
        @csrf
        @if($esEdicion)
            @method('PUT')
        @endif

        <fieldset {{ $inactivo ? 'disabled' : '' }}>
        <div class="space-y-8 mb-6">
            <p class="separador">Información Personal del Alumno</p>
            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="DNI" required />
                    <input name="dni" x-model="alumnoData.dni"
                        @input="alumnoData.dni = alumnoData.dni.replace(/[^0-9]/g, ''); limpiarError('dni')"    
                    placeholder="Documento" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div x-show="errors.dni" x-text="errors.dni" class="text-xs text-red-600 mt-1"></div>
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Nombre" required />
                    <input name="nombre" x-model="alumnoData.nombre"
                        @input="alumnoData.nombre = alumnoData.nombre.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s']/g, ''); limpiarError('nombre')"
                        placeholder="Nombres" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div x-show="errors.nombre" x-text="errors.nombre" class="text-xs text-red-600 mt-1"></div>
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Apellido" required />
                    <input name="apellido" x-model="alumnoData.apellido"
                        @input="alumnoData.apellido = alumnoData.apellido.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s']/g, ''); limpiarError('apellido')"
                        placeholder="Apellidos" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div x-show="errors.apellido" x-text="errors.apellido" class="text-xs text-red-600 mt-1"></div>
                </div>

                <x-campo-fecha-edad
                    label="Fecha de nacimiento"
                    name="fecha_nacimiento"
                    edad-name="edad"
                    required

                    {{-- 1. Le decimos al componente qué variables de Alpine usar --}}
                    model-fecha="alumnoData.fecha_nacimiento"
                    model-edad="alumnoData.edad"

                    {{-- 
                    2. Le inyectamos NUESTRA lógica conectada a alumnoData.
                    (Como pasamos 'x-data', el componente desactivará su lógica interna).
                    --}}
                    x-data="{
                        errorFuturo: false,

                        calcularEdad() {
                            let fecha = alumnoData.fecha_nacimiento;
                            if (!fecha) { 
                                alumnoData.edad = ''; 
                                return; 
                            }
                            
                            const hoy = new Date();
                            const nacimiento = new Date(fecha);

                            if (nacimiento > hoy) {
                                this.errorFuturo = true;
                                alumnoData.fecha_nacimiento = ''; // Borramos
                                alumnoData.edad = '';
                                return;
                            }
                            let edadCalc = hoy.getFullYear() - nacimiento.getFullYear();
                            const mes = hoy.getMonth() - nacimiento.getMonth();
                            
                            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                                edadCalc--;
                            }
                            
                            alumnoData.edad = edadCalc >= 0 ? edadCalc : '';
                        }
                    }"
                    
                    {{-- 3. Ejecutamos el cálculo al iniciar (para modo Editar) y al cambiar --}}
                    x-init="calcularEdad()"
                    @change="calcularEdad()"
                    @input="calcularEdad()"
                    >

                    {{-- Errores dentro del slot --}}
                    <div x-show="errors.fecha_nacimiento" x-text="errors.fecha_nacimiento" class="text-xs text-red-600 mt-1"></div>
                    <div x-show="errorFuturo" class="text-xs text-red-600 mt-1" style="display: none;">
                        La fecha no puede ser futura.
                    </div>
                </x-campo-fecha-edad>

            <div class="fila-botones mt-8">
                <div class="flex flex-col w-1/5">
                    <p class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</p>
                    <input name="nacionalidad" x-model="alumnoData.nacionalidad"
                        @input="alumnoData.nacionalidad = alumnoData.nacionalidad.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s']/g, '')"
                        placeholder="Nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Aula" required />
                    <select name="aula" id="aula"  x-model="alumnoData.aula" @change="limpiarError('aula')"
                        class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar aula</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso }}">{{ $curso }}</option>
                        @endforeach
                    </select>
                    <div x-show="errors.aula" x-text="errors.aula" class="text-xs text-red-600 mt-1"></div>
                </div>
                <div class="flex flex-col w-1/5">
                    <x-campo-requerido text="Cantidad inasistencias" required />
                    <input name="inasistencias" type="text" inputmode="numeric" x-model="alumnoData.inasistencias"
                        @input="alumnoData.inasistencias = String(alumnoData.inasistencias).replace(/[^0-9]/g, ''); limpiarError('inasistencias')"
                        placeholder="Inasistencias" type="number" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div x-show="errors.inasistencias" x-text="errors.inasistencias" class="text-xs text-red-600 mt-1"></div>
                </div>
                <div class="space-y-2">
                    <x-campo-requerido text="Tiene CUD" required />
                    <x-opcion-unica 
                        :items="['Sí', 'No']"
                        name="cud"
                        layout="horizontal"
                        x-model="alumnoData.cud"
                    />
                </div>
            </div>
        </div>

        <div class="space-y-8 mb-6">
            <p class="separador">Red Familiar</p>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellido</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relacion</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefono</th>
                            <th class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Bucle para mostrar familiares temporales cargados --}}
                        <template x-for="(familiar, indice) in familiares" :key="indice">
                            <tr @click="prepararEdicionFamiliar(indice)" class="cursor-pointer hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.nombre"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.apellido"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.dni"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.parentesco ? familiar.parentesco : 'Hermano Alumno'"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" x-text="familiar.telefono_personal"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                    {{-- 'familiar' es el discriminante que sirve para evaluar a posterior en el back en que array de eliminacion se debe agregar
                                        el id del familiar que existe en la tabla, ya que este familiar puede ser un "familiar puro" o un "familiar hermano alumno" --}}
                                    <button @click.prevent.stop="gestionarEliminacion(indice, familiar.parentesco ? 'familiar' : 'hermano')"
                                        type="button" class="text-gray-400 hover:text-red-600 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <button type="button" @click="prepararCreacionFamiliar" class="btn-aceptar">Crear Familiar</button>
        </div>

        <div class="space-y-8 mb-6">
            <p class="separador">Situación Integral</p>
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación socioeconómica </label>
            <textarea name="situacion_socioeconomica" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.situacion_socioeconomica"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación familiar</label>
            <textarea name="situacion_familiar" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.situacion_familiar"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación medica </label>
            <textarea  name="situacion_medica" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.situacion_medica"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Situación escolar </label>
            <textarea name="situacion_escolar" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.situacion_escolar"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Actividades extraescolares </label>
            <textarea name="actividades_extraescolares" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.actividades_extraescolares"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Intervenciones externas</label>
            <textarea name="intervenciones_externas" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.intervenciones_externas"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Antecedentes</label>
            <textarea name="antecedentes" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.antecedentes"></textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" rows="2" x-model="alumnoData.observaciones"></textarea>      
        </div>
        </fieldset>
       
        <div class="space-y-8">
            <p class="separador">Documentación</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-subir">Examinar</button>
                        <span class="text-sm text-gray-500">Solo archivos en formato pdf, jpeg, png o doc con menos de 100Kb</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Cargados:</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                            <span class="text-sm text-gray-600">Documento1.pdf</span>
                            <button type="button" class="text-gray-500 hover:text-red-500">
                            </button>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-100 rounded-md">
                            <span class="text-sm text-gray-600">Documento2.pdf</span>
                            <button type="button" class="text-gray-500 hover:text-red-500">
                            </button>
                        </div>
                    </div>
                </div>
        </div>

        <div class="fila-botones mt-8">
            @if(!$inactivo)
                <button type="button" class="btn-aceptar" @click="validarYGuardar()">Guardar</button>
            @endif   
        </div>
    </form>
   
    @if($esEdicion)
    <div class="mt-4 my-4 flex justify-between items-center">
        <div class="text-sm text-red-600 min-h-[1.5rem]">
            @if($inactivo)
                <p class="text-red-600 text-sm">Este alumno está inactivo. No se permiten modificaciones.</p>
            @endif
        </div>

        <div class="flex space-x-4">
            <x-boton-estado 
                :activo="$alumno->persona->activo" 
                :route="route('alumnos.cambiarActivo', $alumno->id_alumno)" 
            />
            <a class="btn-volver" href="{{ route('alumnos.principal') }}">Volver</a>
        </div>
    </div>
@endif
</div>
@endsection
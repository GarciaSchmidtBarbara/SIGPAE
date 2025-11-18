@extends('layouts.base')

@section('encabezado', 'Crear Familiar')

@section('contenido')
<div x-data="{
    // 1. Inyectamos los datos de PHP
    formData: {{ json_encode($familiarData) }},
    parentesco: '{{ $familiarData['parentesco'] ?? '' }}' || 'padre',
    editIndex: {{ json_encode($indice) }},
    soloLectura: {{ json_encode($solo_lectura) }},

    // 2. Variables de estado visual
    isFilled: false,
    searchQuery: '',
    results: [],
    errors: { nombre: '', apellido: '', dni: '', fecha_nacimiento: '', otro_parentesco: '' },
    dniError: '',

    // 3. Inicialización
    init() {
        if (this.soloLectura) {
            this.isFilled = true;
        }
        
        // Watcher para parentesco
        this.$watch('parentesco', (val) => {
             this.formData.parentesco = val; // Sincronizar
        });
    },

    // 4. Funciones
    validarYGuardar() {
        // Limpiamos errores
        this.errors = {};
        
        // CAMBIO: Validamos campos requeridos usando 'dni'
        let camposRequeridos = [];
        if (this.parentesco !== 'hermano' || (this.parentesco === 'hermano' && !this.isFilled)) {
            camposRequeridos = ['nombre', 'apellido', 'dni', 'fecha_nacimiento']; // <-- ACÁ DECÍA 'documento'
        }

        let errorEncontrado = false;
        
        for (const campo of camposRequeridos) {
            if (!this.formData[campo] || String(this.formData[campo]).trim() === '') {
                // Mensaje personalizado
                this.errors[campo] = `El campo ${campo} es requerido.`;
                errorEncontrado = true;
            }
        }

        // Validación básica de 'otro' parentesco
        if (this.parentesco === 'otro' && !this.formData.otro_parentesco) {
            this.errors.otro_parentesco = 'Debe especificar el parentesco';
            errorEncontrado = true;
        }

        if (!errorEncontrado) {
            this.$refs.form.submit();
        }
    },

    limpiarError(campo) {
        if (this.errors[campo]) delete this.errors[campo];
        if (campo === 'dni') this.dniError = '';
    },
    
    // Placeholders para la Etapa 3
    checkDni() {},
    search() {},
    selectAlumno(al) {}

}" x-init="init()" x-cloak>

    <form method="POST" action="{{ route('familiares.guardar') }}" x-ref="form" novalidate>
        @csrf

        <input type="hidden" name="indice" :value="editIndex">
        <input type="hidden" name="id_familiar" :value="formData.id_familiar">
        <input type="hidden" name="fk_id_persona" :value="formData.fk_id_persona">

        <p class="separador">Relación</p>
        <div class="flex flex-wrap items-center gap-4 mt-2">
            @php($parentescos = ['padre'=>'Padre','madre'=>'Madre','tutor'=>'Tutor','hermano'=>'Hermano','otro'=>'Otro'])
            @foreach($parentescos as $valor => $label)
                <label class="flex items-center gap-2">
                    <input type="radio" name="parentesco" value="{{ $valor }}" x-model="parentesco" class="text-indigo-600 focus:ring-indigo-500">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
            <div x-show="parentesco==='otro'" x-transition class="flex items-center gap-1">
                <input
                    name="otro_parentesco"
                    x-model="formData.otro_parentesco"
                    value="{{ old('otro_parentesco') }}"
                    :disabled="parentesco !== 'otro'"
                    @input="limpiarError('otro_parentesco'); formData.otro_parentesco = formData.otro_parentesco.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                    placeholder="Especificar"
                    class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <span class="text-red-500">*</span>
            </div>
            <template x-if="errors.otro_parentesco">
                <p class="text-red-500 text-sm mt-1" x-text="errors.otro_parentesco"></p>
            </template>
        </div>

        <p class="separador mt-6">Información Personal del Familiar</p>

        <!--Base (o sea, si es padre, madre, tutor u otro)-->
        <template x-if="parentesco !== 'hermano'">
            <div x-cloak class="space-y-4 mt-3">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <x-campo-requerido text="DNI" required />
                        <input name="dni" 
                            x-model="formData.dni"
                            placeholder="dni familiar" 
                            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @input="formData.dni = formData.dni.replace(/[^0-9]/g, '')"
                            @input="limpiarError('dni')"
                            :class="{ 'border-red-500 text-red-700': dniError }">                    
                        <div x-show="dniError" x-text="dniError" class="text-xs text-red-600 mt-1"></div>
                        <div x-show="errors.dni" x-text="errors.dni" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <x-campo-requerido text="Nombre" required />
                        <input name="nombre" x-model="formData.nombre" @input="formData.nombre = formData.nombre.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"

                            placeholder="nombre_familiar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" @input="limpiarError('nombre')">
                        <div x-show="errors.nombre" x-text="errors.nombre" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <x-campo-requerido text="Apellido" required />
                        <input name="apellido" x-model="formData.apellido" @input="formData.apellido = formData.apellido.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="apellido_familiar" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" @input="limpiarError('apellido')">
                        <div x-show="errors.apellido" x-text="errors.apellido" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                        <input name="nacionalidad" x-model="formData.nacionalidad" @input="formData.nacionalidad = formData.nacionalidad.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="Argentina" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Domicilio</label>
                        <input name="domicilio" x-model="formData.domicilio" placeholder="domicilio" @input="formData.domicilio = formData.domicilio.replace(/[^a-zA-Z0-9\s]/g, '')"
                            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Lugar de trabajo</label>
                        <input name="lugar_de_trabajo" x-model="formData.lugar_de_trabajo" @input="formData.lugar_de_trabajo = formData.lugar_de_trabajo.replace(/[^a-zA-Z0-9\s]/g, '')"
                            placeholder="nombre_trabajo" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <x-campo-fecha-edad
                        label="Fec. Nacimiento"
                        name="fecha_nacimiento"
                        edad-name="edad"
                        required

                        {{-- 1. Conectamos a las variables de ESTA vista (formData) --}}
                        model-fecha="formData.fecha_nacimiento"
                        model-edad="formData.edad"
                        
                        {{-- 2. Pasamos el estado de deshabilitado --}}
                        x-bind:disabled="isFilled || soloLectura"

                        {{-- 3. Inyectamos la lógica calculada conectada a formData --}}
                        x-data="{
                            calcularEdad() {
                                let fecha = formData.fecha_nacimiento;
                                if (!fecha) { 
                                    formData.edad = ''; 
                                    return; 
                                }
                                
                                const hoy = new Date();
                                const nacimiento = new Date(fecha);
                                let edadCalc = hoy.getFullYear() - nacimiento.getFullYear();
                                const mes = hoy.getMonth() - nacimiento.getMonth();
                                
                                if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                                    edadCalc--;
                                }
                                
                                formData.edad = edadCalc >= 0 ? edadCalc : '';
                            }
                        }"
                        
                        x-init="calcularEdad()"
                        @change="calcularEdad()"
                    />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Teléfono personal</label>
                        <input name="telefono_personal" x-model="formData.telefono_personal" @input="formData.telefono_personal = formData.telefono_personal.replace(/[^0-9+\-\s]/g, '')"
                            placeholder="221-123456" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Teléfono laboral</label>
                        <input name="telefono_laboral" x-model="formData.telefono_laboral" @input="formData.telefono_laboral = formData.telefono_laboral.replace(/[^0-9+\-\s]/g, '')"
                            placeholder="221-123456" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
        </div> </template>

        <!--Si se marca en el Radio button al hermano-->
        <template x-if="parentesco === 'hermano'">
            <div x-cloak class="space-y-4 mt-3">
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="text-sm font-medium text-gray-700 mb-1">Buscar Alumnos</label>
                        <div class="relative">
                            <input type="text" x-model.debounce.400ms="searchQuery" @input="search()" placeholder="DNI / Nombre / Apellido" class="w-full border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div x-show="results.length" class="absolute z-10 mt-1 w-full bg-white border rounded shadow">
                                <template x-for="al in results" :key="al.id_alumno">
                                    <button type="button" @click="selectAlumno(al)" class="w-full text-left px-3 py-2 hover:bg-gray-100">
                                        <span x-text="al.persona.apellido + ', ' + al.persona.nombre"></span>
                                        <span class="text-xs text-gray-500" x-text="' - DNI ' + al.persona.dni"></span>
                                    </button>
                                </template>
                                <div x-show="results.length===0" class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-aceptar" @click="search()">Buscar</button>
                </div>

                <input type="hidden" name="fk_id_persona" :value="selected?.persona?.id_persona || ''">
                <input type="hidden" name="asiste_a_institucion" :value="selected ? 1 : 0">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <x-campo-requerido text="DNI" required />
                        <input 
                            x-model="formData.dni"
                            :disabled="isFilled || soloLectura"
                            placeholder="dni"
                            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @input="formData.dni = formData.dni.replace(/[^0-9]/g, '')"
                            @input="limpiarError('dni')"
                            :class="{ 'border-red-500 text-red-700': dniError }"
                        >
                        <div x-show="dniError" x-text="dniError" class="text-xs text-red-600 mt-1"></div>
                        <div x-show="errors.dni" x-text="errors.dni" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <x-campo-requerido text="Nombre" required />
                        <input x-model="formData.nombre" :disabled="isFilled || soloLectura" @input="formData.nombre = formData.nombre.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="nombre_hermano" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-show="errors.nombre" x-text="errors.nombre" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <x-campo-requerido text="Apellido" required />
                        <input x-model="formData.apellido" :disabled="isFilled || soloLectura" @input="formData.apellido = formData.apellido.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="apellido_hermano" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-show="errors.apellido" x-text="errors.apellido" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                        <input x-model="formData.nacionalidad" :disabled="isFilled || soloLectura"  @input="formData.nacionalidad = formData.nacionalidad.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Domicilio</label>
                        <input x-model="formData.domicilio" :disabled="isFilled || soloLectura" placeholder="domicilio" @input="formData.domicilio = formData.domicilio.replace(/[^a-zA-Z0-9\s]/g, '')"
                            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex flex-col">
                        <x-campo-requerido text="Fec. Nacimiento" required />
                        <input x-model="formData.fecha_nacimiento" :disabled="isFilled || soloLectura" type="date" :max="new Date().toISOString().split('T')[0]" placeholder="dd/mm/aaaa"
                            class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" @input="calcularEdad()">
                        <div x-show="errors.fecha_nacimiento" x-text="errors.fecha_nacimiento" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Edad</label>
                        <input x-model="formData.edad" :disabled="isFilled || soloLectura" placeholder="edad"  class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-100 text-gray-700">
                    </div>
                </div>

                <!-- Inputs hidden para asegurar que los datos se envíen aunque estén disabled -->
                <template x-if="parentesco==='hermano'">
                    <div>
                        <input type="hidden" name="nombre" :value="formData.nombre">
                        <input type="hidden" name="apellido" :value="formData.apellido">
                        <input type="hidden" name="dni" :value="formData.dni">
                        <input type="hidden" name="fecha_nacimiento" :value="formData.fecha_nacimiento">
                        <input type="hidden" name="edad" :value="formData.edad">
                        <input type="hidden" name="domicilio" :value="formData.domicilio">
                        <input type="hidden" name="nacionalidad" :value="formData.nacionalidad">
                    </div>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex items-center gap-2 col-span-2">
                        <input 
                            id="asiste"
                            name="asiste_a_institucion"
                            type="checkbox"
                            :checked="isFilled"
                            :readonly="true"
                            @click.prevent
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-not-allowed"
                        >
                        <label for="asiste" class="text-sm font-medium text-gray-700 select-none">
                            Asiste a esta institución
                        </label>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Curso</label>
                        <input name="curso" :value="selected?.aula?.curso || ''" disabled placeholder="curso" class="border px-2 py-1 rounded bg-gray-100 text-gray-700">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">División</label>
                        <input name="division" :value="selected?.aula?.division || ''" disabled placeholder="división" class="border px-2 py-1 rounded focus:outline-none bg-gray-100 text-gray-700">
                    </div>
                </div>
            </div>
        </div> </template>

        <div class="mt-4">
            <label class="text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" x-model="formData.observaciones" rows="3"
                class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('observaciones') }}</textarea>
        </div>

        <div class="fila-botones mt-8">
            <button type="button" class="btn-aceptar" @click="validarYGuardar()">Guardar y Volver</button>
            <a href="{{ route('alumnos.continuar') }}" class="btn-volver">Volver</a>
        </div>
    </form>
</div>
@endsection
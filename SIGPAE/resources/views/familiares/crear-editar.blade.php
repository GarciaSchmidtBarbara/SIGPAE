@extends('layouts.base')

@section('encabezado', 'Crear Familiar')

@section('contenido')
<div x-data="{
    // 1. Inyectamos los datos de PHP
    formData: {{ json_encode($familiarData) }},
    parentesco: '{{ $familiarData['parentesco'] ?? '' }}' || 'padre',
    editIndex: {{ json_encode($indice) }},
    soloLectura: {{ json_encode($solo_lectura) }},
    idsEnUso: {{ json_encode($idsEnUso) }},
    searchError: '',

    // 2. Variables de estado visual
    isFilled: false,
    searchQuery: '',
    results: [],
    errors: {
        nombre: '',
        apellido: '',
        dni: '',
        fecha_nacimiento: '',
        otro_parentesco: ''
    },

    dniError: '',

    // 3. Inicialización

    limpiarDatos() {
        this.formData.nombre = '';
        this.formData.apellido = '';
        this.formData.dni = '';
        this.formData.fecha_nacimiento = '';
        this.formData.edad = '';
        this.formData.domicilio = '';
        this.formData.nacionalidad = '';
        this.formData.fk_id_persona = null; // ¡Clave! Rompemos el vínculo
        this.isFilled = false; // Desbloqueamos
        this.selected = null;
        this.searchQuery = '';
    },
    
    init() {
        this.isFilled = false;

        if (this.parentesco === 'hermano' && this.formData.fk_id_persona) {
            this.formData.asiste_a_institucion = true;
        } else {
            this.formData.asiste_a_institucion = !!this.formData.asiste_a_institucion;
        }

        if (this.soloLectura) {
            this.isFilled = true;
        }
        
        this.$watch('parentesco', (val) => {
             this.formData.parentesco = val; 

             // 1. Limpieza de 'otro' 
             if (val !== 'otro') {
                 this.formData.otro_parentesco = '';
                 if (this.errors.otro_parentesco) delete this.errors.otro_parentesco;
             }

             // 2. PROTECCIÓN DE HERMANO ALUMNO (Lo nuevo)
             // Si salimos de 'hermano'...
             if (val !== 'hermano') {
                 // ...Y teníamos datos vinculados (isFilled es true)...
                 // ...Y NO estamos en modo edición estricta (soloLectura)...
                 if (this.isFilled && !this.soloLectura) {
                     // ¡Borramos todo para no guardar un ID fantasma!
                     this.limpiarDatos();
                 }
                 // Si isFilled es false (escrito a mano), NO entra acá y conserva los datos.
             }
        });
    },

    // 4. Funciones
    async validarYGuardar() {
        this.errors = {};
        this.dniError = ''; // (Opcional, si usas errors.dni podés sacarlo)
        let hayErrores = false; // Bandera maestra única

        // 1. Validación Local (Campos Vacíos)
        let camposRequeridos = [];
        if (this.parentesco !== 'hermano' || (this.parentesco === 'hermano' && !this.isFilled)) {
            camposRequeridos = ['nombre', 'apellido', 'dni', 'fecha_nacimiento'];
        }

        for (const campo of camposRequeridos) {
            if (!this.formData[campo] || String(this.formData[campo]).trim() === '') {
                this.errors[campo] = 'Este campo es requerido.';
                hayErrores = true;
            }
        }

        // Validación de 'Otro'
        if (this.parentesco === 'otro' && (!this.formData.otro_parentesco || String(this.formData.otro_parentesco).trim() === '')) {
            this.errors.otro_parentesco = 'Debe especificar el parentesco.';
            hayErrores = true;
        }

        // NOTA: No hacemos return acá para que siga y valide DNI también.

        // 2. Validación Remota de DNI (Semáforo)
        if (this.formData.dni && (!this.isFilled || this.parentesco !== 'hermano')) {
            try {
                const response = await fetch('{{ route("familiares.validar-dni") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ 
                        dni: this.formData.dni, 
                        indice: this.editIndex, 
                        fk_id_persona: this.formData.fk_id_persona 
                    })
                });
                
                if (!response.ok) throw new Error('Error de red');
                const data = await response.json();

                if (!data.valid) {
                    this.errors.dni = data.message; // Usamos errors.dni para uniformidad
                    hayErrores = true;
                }
            } catch (error) {
                console.error(error);
                alert('Error al validar DNI.');
                return;
            }
        }

        // 3. Decisión Final
        if (hayErrores) {
            return;
        }

        this.$refs.form.submit();
    },

    limpiarError(campo) {
        if (this.errors[campo]) delete this.errors[campo];
        if (campo === 'dni') this.dniError = '';
    },
    
    // Función para buscar alumnos en el servidor
    async search() {
        const q = this.searchQuery ? this.searchQuery.trim() : '';
        
        if (!q) { 
            this.results = []; 
            return; 
        }

        try {
            // Asegurate que esta ruta exista en web.php
            const res = await fetch('{{ route('alumnos.buscar') }}?q=' + encodeURIComponent(q));
            
            if (!res.ok) return;
            
            this.results = await res.json();
        } catch(e) { 
            console.error(e); 
        }
    },

    // Función para seleccionar un alumno y llenar el formulario
    selectAlumno(al) {
        this.searchError = ''; 
        let idPersona = al.persona?.id_persona;
        
        // Si el ID está en la lista negra...
        // Convertimos el ID del buscador a String
        let idString = String(idPersona);

        // Buscamos en la lista convirtiendo también cada elemento a String
        let yaExiste = this.idsEnUso.some(id => String(id) === idString);

        console.log('Lista Negra (PHP):', this.idsEnUso);
        console.log('Seleccionado (JS):', idPersona);

        if (idPersona && yaExiste) {
            this.searchError = 'Este alumno ya fue agregado como familiar.';
            this.results = []; // Ocultamos la lista
            return; // FRENAMOS TODO. No carga nada.
        }
        // 1. Guardamos la selección
        this.selected = al; 
        this.results = []; 
        this.searchQuery = al.persona?.dni || ''; 
        
        // 2. RELLENAMOS EL FORMULARIO (Mapeo)
        this.formData.nombre = al.persona?.nombre || '';
        this.formData.apellido = al.persona?.apellido || '';
        this.formData.dni = al.persona?.dni || ''; // Usamos 'dni'
        
        // Formateamos fecha para el input
        this.formData.fecha_nacimiento = al.persona?.fecha_nacimiento 
            ? new Date(al.persona.fecha_nacimiento).toISOString().split('T')[0] 
            : '';
        
        this.formData.edad = al.persona?.edad || '';
        this.formData.domicilio = al.persona?.domicilio || '';
        this.formData.nacionalidad = al.persona?.nacionalidad || '';
        this.formData.curso = al.aula?.curso || ''; 
        this.formData.division = al.aula?.division || '';

        // 3. VINCULACIÓN (Clave para el Back-End)
        this.formData.fk_id_persona = al.persona?.id_persona || null;
        this.formData.asiste_a_institucion = true;

        // 4. ACTIVAMOS EL CANDADO (Bloquea los inputs)
        this.isFilled = true; 
        
        // Limpiamos errores visuales
        this.errors = {};
        
        // Recalculamos edad si hace falta para consistencia visual
        if(this.formData.fecha_nacimiento) {
             // El componente x-campo-fecha-edad lo detectará por x-model
        }
    },

}" x-init="init()" x-cloak>

    <form method="POST" action="{{ route('familiares.guardarYVolver') }}" x-ref="form" novalidate>
        @csrf

        <input type="hidden" name="indice" :value="editIndex">
        <input type="hidden" name="id_familiar" :value="formData.id_familiar">
        <input type="hidden" name="curso" :value="formData.curso">
        <input type="hidden" name="division" :value="formData.division">
        <input type="hidden" name="fk_id_persona" :value="formData.fk_id_persona">
        <input type="hidden" name="asiste_a_institucion" :value="formData.asiste_a_institucion ? 1 : 0">
        
        <p class="separador">Relación</p>
        <div class="flex flex-wrap items-center gap-4 mt-2">
            @php($parentescos = ['padre'=>'Padre','madre'=>'Madre','tutor'=>'Tutor','hermano'=>'Hermano','otro'=>'Otro'])
            @foreach($parentescos as $valor => $label)
                <label class="flex items-center gap-2"
                    {{-- 
                        LÓGICA DE VISIBILIDAD:
                        1. Si estamos creando (editIndex === null) -> Mostrar TODO.
                        2. Si editamos 'hermano' -> Mostrar SOLO la opción 'hermano'.
                        3. Si editamos 'padre', 'madre', 'tutor', u 'otro' -> Mostrar TODO MENOS 'hermano'.
                    --}}
                    x-show="editIndex === null || (parentesco === 'hermano' ? '{{ $valor }}' === 'hermano' : '{{ $valor }}' !== 'hermano')"
                >
                    <input type="radio" 
                        name="parentesco" 
                        value="{{ $valor }}" 
                        x-model="parentesco" 
                        class="text-indigo-600 focus:ring-indigo-500"
                        
                        {{-- CAMBIO 1: Usamos clases para simular el bloqueo visual (cursor) --}}
                        :class="{ 'cursor-not-allowed': soloLectura, 'cursor-pointer': !soloLectura }"
                        
                        {{-- CAMBIO 2: En lugar de disabled, prevenimos el clic si es solo lectura --}}
                        @click="if(soloLectura) $event.preventDefault()"
                    >
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
                            errorFuturo: false, // 1. Nueva bandera para el mensaje

                            calcularEdad() {
                                let fecha = formData.fecha_nacimiento;
                                
                                // Limpiar si está vacío
                                if (!fecha) { 
                                    formData.edad = ''; 
                                    this.errorFuturo = false; // Apagar error si borra
                                    return; 
                                }
                                
                                const hoy = new Date();
                                const nacimiento = new Date(fecha);

                                // 2. VALIDACIÓN SIN ALERT
                                if (nacimiento > hoy) {
                                    this.errorFuturo = true; // Activamos el mensaje rojo
                                    formData.fecha_nacimiento = ''; // Borramos la fecha inválida
                                    formData.edad = '';
                                    return;
                                }
                                
                                this.errorFuturo = false; // Apagamos el error si la fecha es válida

                                // ... (El resto del cálculo de edad sigue igual) ...
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
                    @input="calcularEdad()"
                    >

                    {{-- Errores dentro del slot --}}
                    <div x-show="errors.fecha_nacimiento" x-text="errors.fecha_nacimiento" class="text-xs text-red-600 mt-1"></div>
                    <div x-show="errorFuturo" class="text-xs text-red-600 mt-1" style="display: none;">
                        La fecha no puede ser futura.
                    </div>
                </x-campo-fecha-edad>
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
                <div class="flex items-end gap-3" x-show="editIndex === null">
                    <div class="flex-1">
                        <label class="text-sm font-medium text-gray-700 mb-1">Buscar Alumnos</label>
                        <div class="relative">
                            <input type="text" x-model.debounce.400ms="searchQuery" @input="search()" placeholder="DNI / Nombre / Apellido" class="w-full border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div x-show="searchError" x-text="searchError" class="text-xs text-red-600 mt-1 font-bold"></div>
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

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <x-campo-requerido text="DNI" required />
                        <input 
                            name="dni"
                            x-model="formData.dni"
                            x-bind:readonly="isFilled || soloLectura"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isFilled || soloLectura }"
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
                        <input name="nombre" x-model="formData.nombre" x-bind:readonly="isFilled || soloLectura"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isFilled || soloLectura }"
                            @input="formData.nombre = formData.nombre.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="nombre_hermano" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-show="errors.nombre" x-text="errors.nombre" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <x-campo-requerido text="Apellido" required />
                        <input name="apellido" x-model="formData.apellido" x-bind:readonly="isFilled || soloLectura"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isFilled || soloLectura }"
                            @input="formData.apellido = formData.apellido.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="apellido_hermano" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-show="errors.apellido" x-text="errors.apellido" class="text-xs text-red-600 mt-1"></div>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
                        <input name="nacionalidad" x-model="formData.nacionalidad" x-bind:readonly="isFilled || soloLectura"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isFilled || soloLectura }"
                            @input="formData.nacionalidad = formData.nacionalidad.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            placeholder="nacionalidad" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Domicilio</label>
                        <input name="domicilio" x-model="formData.domicilio" x-bind:readonly="isFilled || soloLectura"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isFilled || soloLectura }"
                            @input="formData.domicilio = formData.domicilio.replace(/[^a-zA-Z0-9\s]/g, '')"
                            placeholder="domicilio" class="border px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <x-campo-fecha-edad
                        label="Fec. Nacimiento"
                        name="fecha_nacimiento"
                        edad-name="edad"
                        required
                        model-fecha="formData.fecha_nacimiento"
                        model-edad="formData.edad"
                        
                        condicion-readonly="isFilled || soloLectura"

                        {{-- Lógica mejorada con Watcher --}}
                        x-data="{
                            errorFuturo: false,

                            init() {
                                // 1. Calculamos al arrancar (para Edición)
                                this.calcularEdad();

                                // 2. ¡LA CLAVE! Vigilamos cambios automáticos (para el Buscador)
                                this.$watch('formData.fecha_nacimiento', (val) => {
                                    this.calcularEdad();
                                });
                            },

                            calcularEdad() {
                                let fecha = formData.fecha_nacimiento;
                                
                                if (!fecha) { 
                                    formData.edad = ''; 
                                    this.errorFuturo = false;
                                    return; 
                                }
                                
                                const hoy = new Date();
                                const nacimiento = new Date(fecha);
                                
                                if (nacimiento > hoy) {
                                    this.errorFuturo = true;
                                    formData.fecha_nacimiento = ''; 
                                    formData.edad = '';
                                    return;
                                }
                                
                                this.errorFuturo = false;

                                let edadCalc = hoy.getFullYear() - nacimiento.getFullYear();
                                const mes = hoy.getMonth() - nacimiento.getMonth();
                                
                                if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                                    edadCalc--;
                                }
                                
                                formData.edad = edadCalc >= 0 ? edadCalc : '';
                            }
                        }"
                        
                        {{-- Usamos el init completo en lugar de llamar a la función suelta --}}
                        x-init="init()"
                        @change="calcularEdad()"
                        @input="calcularEdad()"
                    >
                        {{-- Errores dentro del slot --}}
                        <div x-show="errors.fecha_nacimiento" x-text="errors.fecha_nacimiento" class="text-xs text-red-600 mt-1"></div>
                        <div x-show="errorFuturo" class="text-xs text-red-600 mt-1" style="display: none;">
                            La fecha no puede ser futura.
                        </div>
                    </x-campo-fecha-edad>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex items-center gap-2 col-span-2">
                        <input 
                            id="asiste" 
                            name="asiste_a_institucion" 
                            type="checkbox" 
                            x-model="formData.asiste_a_institucion"
                            :class="{ 'bg-gray-100 cursor-not-allowed': isFilled || soloLectura }"
                            @click.prevent
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-not-allowed"
                        >
                        <label for="asiste" class="text-sm font-medium text-gray-700 select-none">
                            Asiste a esta institución
                        </label>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Curso</label>
                        <input 
                            name="curso" 
                            x-model="formData.curso" 
                            :disabled="true" 
                            placeholder="Curso" 
                            class="border px-2 py-1 rounded bg-gray-100 text-gray-700 cursor-not-allowed"
                        >
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">División</label>
                        <input 
                            name="division" 
                            x-model="formData.division" 
                            :disabled="true" 
                            placeholder="División" 
                            class="border px-2 py-1 rounded bg-gray-100 text-gray-700 cursor-not-allowed"
                        >
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
            <a href="{{ route('alumnos.crear') }}" class="btn-volver">Volver</a>
        </div>
    </form>
</div>
@endsection